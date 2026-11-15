<?php
declare(strict_types=1);
require __DIR__ . '/common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Metodo non consentito.'); }
if (empty($c['admin_user']) || empty($c['admin_password_hash'])) { http_response_code(503); exit('Accesso non configurato.'); }
limit_requests($db,$c,'registro',20,3600);
$user=(string)($_SERVER['PHP_AUTH_USER']??'');$pass=(string)($_SERVER['PHP_AUTH_PW']??'');
if (!hash_equals((string)$c['admin_user'],$user) || !password_verify($pass,$c['admin_password_hash'])) { header('WWW-Authenticate: Basic realm="Registro AFANT", charset="UTF-8"');http_response_code(401);exit('Autenticazione richiesta.'); }
require __DIR__ . '/registro-xlsx.php';
try { $book=costruisci_registro($db,$storage,true); }
catch (Throwable $e) { error_log('AFANT: archivio storico non disponibile'); http_response_code(503); exit('Registro temporaneamente non disponibile.'); }
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="iscrizioni-afant.xlsx"');
(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
