<?php
declare(strict_types=1);
require __DIR__ . '/validation.php';
require dirname(__DIR__) . '/vendor/autoload.php';
$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) { http_response_code(503); exit('Servizio non configurato.'); }
$c = require $configFile;
// Separare la chiave del limite richieste dalla chiave Turnstile senza
// richiedere un ulteriore segreto da inserire manualmente.
if (empty($c['rate_secret']) && !empty($c['turnstile_secret'])) {
    $c['rate_secret'] = hash_hmac('sha256', 'afant-rate-limiting-v1', $c['turnstile_secret']);
}
foreach (['site_host','smtp_host','smtp_user','smtp_password','from_email','recipient','turnstile_secret','storage_dir','rate_secret'] as $key) {
    if (empty($c[$key])) { http_response_code(503); exit('Servizio non configurato.'); }
}
if (empty($c['enabled']) || !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') { http_response_code(503); exit('Servizio non disponibile.'); }
$storage = realpath($c['storage_dir']);
$docroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
if (!$storage || !$docroot || str_starts_with($storage . '/', $docroot . '/') || !is_writable($storage)) { http_response_code(503); exit('Archivio non configurato.'); }
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
umask(0077);
try {
    $db = new PDO('sqlite:' . $storage . '/registro.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('PRAGMA busy_timeout=5000');
    $db->exec('CREATE TABLE IF NOT EXISTS iscrizioni (id TEXT PRIMARY KEY, ricevuta_il TEXT NOT NULL, anno INTEGER NOT NULL, dati TEXT NOT NULL, allegati TEXT NOT NULL, notifica TEXT NOT NULL)');
    $db->exec('CREATE TABLE IF NOT EXISTS tentativi (categoria TEXT NOT NULL, impronta TEXT NOT NULL, momento INTEGER NOT NULL)');
} catch (Throwable $e) { error_log('AFANT: archivio non disponibile'); http_response_code(503); exit('Servizio temporaneamente non disponibile.'); }
function limit_requests(PDO $db, array $c, string $category, int $max, int $period): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = hash_hmac('sha256', $ip, $c['rate_secret']); $now=time();
    $db->beginTransaction();
    try {
        $del=$db->prepare('DELETE FROM tentativi WHERE momento < ?'); $del->execute([$now-86400]);
        $q=$db->prepare('SELECT COUNT(*) FROM tentativi WHERE categoria=? AND impronta=? AND momento>=?');$q->execute([$category,$key,$now-$period]);
        if ((int)$q->fetchColumn() >= $max) { $db->rollBack(); http_response_code(429); exit('Troppi tentativi. Riprova più tardi.'); }
        $q=$db->prepare('INSERT INTO tentativi VALUES(?,?,?)');$q->execute([$category,$key,$now]);$db->commit();
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
}
function captcha_result_valid(mixed $json, int $status, string $host, string $action): bool {
    return $status === 200 && is_array($json) && ($json['success'] ?? false) === true && ($json['hostname'] ?? '') === $host && ($json['action'] ?? '') === $action;
}
function verify_captcha(string $token, array $c, string $action): bool {
    if (strlen($token) < 1 || strlen($token) > 2048) return false;
    $ch=curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['secret'=>$c['turnstile_secret'],'response'=>$token]),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
    $result=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
    $json=is_string($result) ? json_decode($result,true) : null;
    return captcha_result_valid($json, $status, $c['site_host'], $action);
}
function create_mailer(array $c): PHPMailer\PHPMailer\PHPMailer {
    $m=new PHPMailer\PHPMailer\PHPMailer(true);$m->isSMTP();$m->Host=$c['smtp_host'];$m->Port=(int)($c['smtp_port']??465);$m->SMTPAuth=true;$m->Username=$c['smtp_user'];$m->Password=$c['smtp_password'];$m->SMTPSecure=PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;$m->CharSet='UTF-8';$m->setFrom($c['from_email'],'A.FA.N.T. Arezzo');return $m;
}
