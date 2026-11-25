<?php
declare(strict_types=1);
require __DIR__ . '/common.php';
require_once __DIR__ . '/email-template.php';
header('Content-Type: text/plain; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit('Metodo non consentito.'); }
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== 'https://' . $c['site_host']) { http_response_code(403); exit('Richiesta non consentita.'); }
try {
    limit_requests($db,$c,'moduli',12,3600);
    $data=validate_form($_POST);
    if (!verify_captcha((string)($_POST['cf-turnstile-response']??''),$c,'afant_' . $data['tipo'])) { http_response_code(400); exit('Verifica antispam non riuscita. Riprova.'); }
    $field=$data['tipo']==='contatto' ? 'allegati' : 'ricevuta';$f=$_FILES[$field]??null;$files=[];$total=0;
    if (is_array($f)) {
        $names=is_array($f['name']) ? $f['name'] : [$f['name']];
        foreach ($names as $i=>$name) {
            $err=is_array($f['error'])?$f['error'][$i]:$f['error'];if ($err===UPLOAD_ERR_NO_FILE) continue;
            $size=is_array($f['size'])?$f['size'][$i]:$f['size'];$tmp=is_array($f['tmp_name'])?$f['tmp_name'][$i]:$f['tmp_name'];
            if ($err!==UPLOAD_ERR_OK || !is_uploaded_file($tmp) || ++$total>3 || $size>5*1024*1024) throw new InvalidArgumentException('Allega al massimo 3 file da 5 MB ciascuno.');
            $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);$allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
            if (!isset($allowed[$mime])) throw new InvalidArgumentException('Sono ammessi solo PDF, JPG e PNG.');
            $files[]=['tmp'=>$tmp,'nome'=>'allegato-'.($i+1).'.'.$allowed[$mime],'mime'=>$mime];
        }
    }
    $id=bin2hex(random_bytes(12));$now=new DateTimeImmutable('now',new DateTimeZone('Europe/Rome'));$stored=[];
    if ($data['tipo']==='iscrizione') {
        $dir=$storage.'/allegati'; if (!is_dir($dir) && !mkdir($dir,0700,true)) throw new RuntimeException('Archivio allegati non disponibile');
        foreach ($files as $i=>$f) { $name=$id.'-'.$i.'.'.pathinfo($f['nome'],PATHINFO_EXTENSION);if (!move_uploaded_file($f['tmp'],$dir.'/'.$name)) throw new RuntimeException('Salvataggio allegato non riuscito');chmod($dir.'/'.$name,0600);$stored[]=$name;$files[$i]['tmp']=$dir.'/'.$name; }
        $q=$db->prepare('INSERT INTO iscrizioni VALUES(?,?,?,?,?,?)');$q->execute([$id,$now->format('c'),(int)($data['anno_iscrizione']??$now->format('Y')),json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),json_encode($stored,JSON_THROW_ON_ERROR),'da inviare']);
        // Tiene aggiornato il file Excel nella cartella privata; un errore qui non blocca l'iscrizione (i dati sono già nel registro).
        require_once __DIR__ . '/registro-xlsx.php';
        $registroFile=null;
        try { $registroFile=salva_registro_aggiornato($db,$storage); } catch (Throwable $e) { error_log('AFANT: file registro non aggiornato, riferimento '.$id.': '.get_class($e)); }
    }
    $m=create_mailer($c);$m->addAddress($c['recipient']);if($data['email']!=='')$m->addReplyTo($data['email'],$data['nome'].' '.$data['cognome']);
    $m->Subject=$data['tipo']==='contatto'?'Richiesta dal sito AFANT':'Richiesta iscrizione AFANT '.($data['anno_iscrizione']??$now->format('Y'));
    $body="Riferimento: $id\nData: ".$now->format('d/m/Y H:i')."\n\n";
    foreach ($data as $k=>$v) if ($k!=='tipo') $body.=str_replace('_',' ',ucfirst($k)).': '.$v."\n";
    [$htmlW,$testoW]=email_webmaster($data,$id,$now,array_column($files,'nome'),$body);
    $m->isHTML(true);$m->Body=$htmlW;$m->AltBody=$testoW;foreach($files as $f)$m->addAttachment($f['tmp'],$f['nome']);
    if (!empty($c['allega_registro']) && !empty($registroFile) && is_file($registroFile)) $m->addAttachment($registroFile,'iscrizioni-afant-aggiornato.xlsx');
    try { $m->send(); if($data['tipo']==='iscrizione'){$q=$db->prepare('UPDATE iscrizioni SET notifica=? WHERE id=?');$q->execute(['inviata',$id]);} }
    catch(Throwable $e){error_log('AFANT: invio non riuscito, riferimento '.$id);http_response_code(503);exit($data['tipo']==='iscrizione'?'La richiesta è stata registrata, ma la notifica non è partita. Contatta AFANT indicando il riferimento '.$id.'.':'Invio non riuscito. Riprova oppure scrivi a info@afantarezzo.it.');}
    $sent=false; if($data['email']!=='') try { $receipt=create_mailer($c);$receipt->addAddress($data['email']);$receipt->addReplyTo($c['recipient']);$receipt->Subject='Conferma ricezione richiesta - AFANT Arezzo';[$htmlR,$testoR]=email_ricevuta($data,$id,$now);$receipt->isHTML(true);$receipt->Body=$htmlR;$receipt->AltBody=$testoR;$receipt->send();$sent=true; } catch(Throwable $e) { error_log('AFANT: ricevuta non inviata, riferimento '.$id);$sent=false; }
    echo 'Richiesta inviata. Riferimento: '.$id.($data['email']===''?'':($sent?' Ti abbiamo inviato una conferma via email.':' La conferma via email non è stata inviata.'));
} catch (InvalidArgumentException $e) { http_response_code(400);exit($e->getMessage()); }
catch (Throwable $e) { error_log('AFANT: errore elaborazione modulo');http_response_code(503);exit('Servizio temporaneamente non disponibile.'); }
