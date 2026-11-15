<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/validation.php';
function check(bool $x,string $message):void{if(!$x){fwrite(STDERR,"FAIL: $message\n");exit(1);}}
function rejects(array $p):bool{try{validate_form($p);return false;}catch(InvalidArgumentException $e){return true;}}
$p=['tipo'=>'contatto','nome'=>'Anna','cognome'=>'Rossi','email'=>'anna@example.org','conferma_email'=>'anna@example.org','telefono'=>'','oggetto'=>'Informazioni','messaggio'=>'Buongiorno','privacy'=>'on'];
check(validate_form($p)['email']==='anna@example.org','valida contatto');
$q=$p;$q['conferma_email']='diverso@example.org';check(rejects($q),'email diverse');
$q=$p;$q['oggetto']="test\x00male";check(rejects($q),'caratteri di controllo');
$q=$p;unset($q['privacy']);check(rejects($q),'privacy obbligatoria');
$q=$p;$q['tipo']='ignoto';check(rejects($q),'tipo inesistente');
$p=['tipo'=>'iscrizione','nome'=>'Anna','cognome'=>'Rossi','email'=>'anna@example.org','conferma_email'=>'anna@example.org','pec'=>'anna@example.org','conferma_pec'=>'anna@example.org','codice_fiscale'=>'RSSNNA90A01A390X','causale'=>'Rinnovo','telefono'=>'12345','indirizzo'=>'Via Roma 1','localita'=>'Arezzo','provincia'=>'AR','cap'=>'52100','paese'=>'Italia','luogo_nascita'=>'Arezzo','data_nascita'=>'1990-01-01','iscritto_precedente'=>'si','pagamento'=>'bonifico','privacy'=>'on'];
check(validate_form($p)['tipo']==='iscrizione','iscrizione valida');$q=$p;$q['data_nascita']='2099-01-01';check(rejects($q),'data futura');$q=$p;$q['codice_fiscale']='x';check(rejects($q),'codice fiscale');
$q=$p;$cur=(int)date('Y');$q['anno_iscrizione']=(string)($cur+1);check(validate_form($q)['anno_iscrizione']===(string)($cur+1),'anno iscrizione prossimo');
$q=$p;$q['anno_iscrizione']=(string)($cur+2);check(rejects($q),'anno iscrizione lontano');
$q=$p;$q['anno_iscrizione']='abc';check(rejects($q),'anno iscrizione non numerico');
check(validate_form($p)['anno_iscrizione']===(string)$cur,'anno iscrizione predefinito');
$q=$p;unset($q['pec'],$q['conferma_pec']);check(validate_form($q)['pec']==='','PEC facoltativa assente');
$q=$p;$q['pec']='';$q['conferma_pec']='';check(validate_form($q)['pec']==='','PEC facoltativa vuota');
$q=$p;$q['conferma_pec']='diversa@pec.example.org';check(rejects($q),'PEC indicata ma diversa dalla conferma');
check(captcha_diagnosi(['success'=>false,'error-codes'=>['invalid-input-secret']],200,'','www.afantarezzo.it','afant_contatto')==='rifiutato da Cloudflare: invalid-input-secret','codice Cloudflare nel registro');
check(captcha_diagnosi(['success'=>false,'error-codes'=>['timeout-or-duplicate']],200,'','www.afantarezzo.it','afant_contatto')==='rifiutato da Cloudflare: timeout-or-duplicate','token scaduto nel registro');
check(str_contains(captcha_diagnosi(null,0,'Could not resolve host: challenges.cloudflare.com','www.afantarezzo.it','afant_contatto'),'connessione a Cloudflare non riuscita'),'errore di rete nel registro');
check(str_contains(captcha_diagnosi(['success'=>true,'hostname'=>'nuovo.afantarezzo.it','action'=>'afant_contatto'],200,'','www.afantarezzo.it','afant_contatto'),'dominio diverso'),'dominio diverso');
check(str_contains(captcha_diagnosi(['success'=>true,'hostname'=>'www.afantarezzo.it','action'=>'altra'],200,'','www.afantarezzo.it','afant_contatto'),'azione diversa'),'azione diversa');
check(captcha_diagnosi(['success'=>true,'hostname'=>'www.afantarezzo.it','action'=>'afant_contatto'],200,'','www.afantarezzo.it','afant_contatto')==='valido','valido');
check(!str_contains(captcha_diagnosi(['success'=>false,'error-codes'=>["x\n<script>"]],200,'','h','a'),'<'),'nessun carattere pericoloso nel registro');
echo "Controlli validazione superati\n";
