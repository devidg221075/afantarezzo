<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
$hash = '';
$errore = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 16 || strlen($password) > 72) {
        $errore = 'Inserisci una password da 16 a 72 caratteri.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?><!doctype html><html lang="it"><head><meta charset="utf-8"><title>Accesso registro AFANT</title><style>body{font:18px system-ui;max-width:700px;margin:4em auto;padding:0 1em;line-height:1.6}input,textarea,button{font:inherit;max-width:100%;box-sizing:border-box}input,textarea{width:100%;padding:.6em}button{padding:.6em 1em}textarea{height:7em}</style></head><body>
<h1>Prepara la password del registro AFANT</h1><p>Questa pagina funziona solo sul tuo computer. Scegli una password diversa da quella della posta e non scriverla nella chat.</p>
<?php if ($errore): ?><p><?=htmlspecialchars($errore, ENT_QUOTES, 'UTF-8')?></p><?php endif; ?>
<form method="post"><label>Password del registro (almeno 16 caratteri)<input type="password" name="password" minlength="16" maxlength="72" required autocomplete="new-password"></label><p><button type="submit">Genera codice per cPanel</button></p></form>
<?php if ($hash): ?><p>Copia il codice qui sotto e inseriscilo nel campo <code>admin_password_hash</code> del file di configurazione privata.</p><textarea readonly aria-label="Codice da copiare"><?=htmlspecialchars($hash, ENT_QUOTES, 'UTF-8')?></textarea><?php endif; ?>
</body></html>
