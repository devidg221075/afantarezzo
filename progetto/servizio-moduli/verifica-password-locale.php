<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $hash = (string) ($_POST['hash'] ?? '');
    $result = strlen($password) > 0 && preg_match('/^\$2[aby]\$\d\d\$[.\/A-Za-z0-9]{53}$/D', $hash) === 1 && password_verify($password, $hash);
}
?><!doctype html><html lang="it"><head><meta charset="utf-8"><title>Verifica accesso registro</title><style>body{font:18px system-ui;max-width:700px;margin:3em auto;padding:0 1em;line-height:1.6}input,textarea,button{font:inherit;max-width:100%;box-sizing:border-box}input,textarea{width:100%;padding:.6em}textarea{height:6em}button{padding:.6em 1em}</style></head><body>
<h1>Verifica password del registro</h1><p>Questa pagina funziona soltanto sul tuo computer. Non inserire qui la password della posta. Non memorizza la password né il codice.</p>
<?php if ($result !== null): ?><p><strong><?= $result ? 'La password corrisponde al codice.' : 'La password non corrisponde al codice oppure il codice non è completo.' ?></strong></p><?php endif; ?>
<form method="post"><label>Codice presente nel campo admin_password_hash<textarea name="hash" required spellcheck="false"></textarea></label><label>Password del registro<input type="password" name="password" required autocomplete="off"></label><p><button type="submit">Verifica sul computer</button></p></form>
</body></html>
