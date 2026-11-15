<?php
declare(strict_types=1);
// Copiare come config.php nella cartella privata, fuori da ogni directory pubblica.
return [
    'enabled' => false,
    'site_host' => 'nuovo.afantarezzo.it',
    'smtp_host' => 'mail.afantarezzo.it',
    'smtp_port' => 465,
    'smtp_user' => 'noreply@afantarezzo.it',
    'smtp_password' => '',
    'from_email' => 'noreply@afantarezzo.it',
    'recipient' => 'info@afantarezzo.it',
    'turnstile_secret' => '',
    'admin_user' => '',
    'admin_password_hash' => '',
    'rate_secret' => '',
    // true (scelta del cliente, 15 novembre 2026): ogni nuova iscrizione arriva via email con il registro Excel aggiornato in allegato.
    // Il file contiene i dati di tutti gli iscritti: la casella 'recipient' deve essere protetta e usata solo dal Consiglio Direttivo.
    'allega_registro' => true,
    'storage_dir' => '/home/afantarezzo/afant-privato/dati',
];
