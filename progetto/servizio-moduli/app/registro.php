<?php
declare(strict_types=1);
require __DIR__ . '/common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Metodo non consentito.'); }
if (empty($c['admin_user']) || empty($c['admin_password_hash'])) { http_response_code(503); exit('Accesso non configurato.'); }
limit_requests($db,$c,'registro',20,3600);
$user=(string)($_SERVER['PHP_AUTH_USER']??'');$pass=(string)($_SERVER['PHP_AUTH_PW']??'');
if (!hash_equals((string)$c['admin_user'],$user) || !password_verify($pass,$c['admin_password_hash'])) { header('WWW-Authenticate: Basic realm="Registro AFANT", charset="UTF-8"');http_response_code(401);exit('Autenticazione richiesta.'); }
$rows=$db->query('SELECT * FROM iscrizioni ORDER BY ricevuta_il DESC')->fetchAll(PDO::FETCH_ASSOC);
$cols=['Anno richiesta','Data richiesta','Nome','Cognome','Codice fiscale','Causale iscrizione','Iscritto anno precedente','Email','PEC','Telefono','Indirizzo','Località','Provincia o regione','CAP','Paese','Luogo di nascita','Data di nascita','Ordine di appartenenza','Luogo di lavoro','Metodo di pagamento','Riferimento ricevuta','Note aggiuntive','Privacy letta','Stato notifica','Riferimento'];
$keys=['anno','ricevuta_il','nome','cognome','codice_fiscale','causale','iscritto_precedente','email','pec','telefono','indirizzo','localita','provincia','cap','paese','luogo_nascita','data_nascita','ordine','luogo_lavoro','pagamento','allegati','note','privacy','notifica','id'];
$book=new PhpOffice\PhpSpreadsheet\Spreadsheet();$sheet=$book->getActiveSheet();$sheet->setTitle('Iscrizioni');
foreach($cols as $i=>$v) $sheet->setCellValueExplicit([$i+1,1],$v,PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
foreach($rows as $n=>$row){$data=json_decode($row['dati'],true,512,JSON_THROW_ON_ERROR);$data['anno']=(string)$row['anno'];$data['ricevuta_il']=$row['ricevuta_il'];$data['allegati']=implode(', ',json_decode($row['allegati'],true,512,JSON_THROW_ON_ERROR));$data['privacy']='Sì';$data['notifica']=$row['notifica'];$data['id']=$row['id'];foreach($keys as $i=>$key)$sheet->setCellValueExplicit([$i+1,$n+2],(string)($data[$key]??''),PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);}
$sheet->freezePane('A2');$sheet->setAutoFilter($sheet->calculateWorksheetDimension());
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="iscrizioni-afant.xlsx"');
(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
