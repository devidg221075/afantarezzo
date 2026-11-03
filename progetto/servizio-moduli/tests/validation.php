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
echo "Controlli validazione superati\n";
