<?php
declare(strict_types=1);

// Email in formato HTML (con testo semplice come alternativa) per il richiedente e per il webmaster.
// Tutti i valori vengono escaped: i dati del modulo non vengono mai inseriti come HTML.

function email_e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function email_involucro(string $titolo, string $sottotitolo, string $corpo, string $piede): string {
    return '<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . email_e($titolo) . '</title></head>'
        . '<body style="margin:0;padding:0;background:#eef4fb;font-family:Arial,Helvetica,sans-serif;color:#14243a">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4fb;padding:24px 12px"><tr><td align="center">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden">'
        . '<tr><td style="background:#1f5fa8;background-image:linear-gradient(120deg,#1f5fa8,#1aa7c9 60%,#6a4fb3);padding:24px 28px;color:#ffffff">'
        . '<div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;opacity:.9">' . email_e($sottotitolo) . '</div>'
        . '<div style="font-size:22px;font-weight:bold;margin-top:6px">' . email_e($titolo) . '</div></td></tr>'
        . $corpo
        . '<tr><td style="padding:16px 28px;background:#f6f8fb;font-size:12px;line-height:1.6;color:#5a6b80">' . $piede . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

function email_ricevuta(array $data, string $id, DateTimeImmutable $now): array {
    $iscr = ($data['tipo'] ?? '') === 'iscrizione';
    $anno = (string)($data['anno_iscrizione'] ?? $now->format('Y'));
    $causale = (string)($data['causale'] ?? ('Iscrizione Afant ' . $anno));
    $prossimi = $iscr
        ? '<p style="margin:0 0 10px;font-size:15px;font-weight:bold;color:#1f5fa8">Prossimi passi</p>'
          . '<p style="margin:0 0 18px;font-size:14px;line-height:1.6">Per completare l\'iscrizione, la quota annuale di 15,00 € si versa con bonifico alle coordinate indicate sul sito, con causale <strong>' . email_e($causale) . '</strong>. Se hai già pagato, invia la ricevuta rispondendo a questa email.</p>'
        : '<p style="margin:0 0 18px;font-size:14px;line-height:1.6">Ti risponderemo appena possibile.</p>';
    $corpo = '<tr><td style="padding:28px">'
        . '<p style="margin:0 0 14px;font-size:16px">Buongiorno ' . email_e($data['nome'] ?? '') . ',</p>'
        . '<p style="margin:0 0 18px;font-size:15px;line-height:1.55">abbiamo ricevuto la tua richiesta' . ($iscr ? ' di <strong>iscrizione ' . email_e($anno) . '</strong>' : '') . '. Grazie per la fiducia.</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f8fd;border-left:4px solid #1aa7c9;border-radius:6px;margin:0 0 20px"><tr><td style="padding:14px 16px;font-size:14px;line-height:1.7">'
        . '<strong>Riferimento:</strong> ' . email_e($id) . '<br><strong>Data:</strong> ' . email_e($now->format('d/m/Y H:i'))
        . ($iscr ? '<br><strong>Causale:</strong> ' . email_e($causale) : '')
        . '</td></tr></table>'
        . $prossimi
        . '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 6px"><tr><td style="background:#1f5fa8;border-radius:8px"><a href="https://www.afantarezzo.it/" style="display:inline-block;padding:12px 22px;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none">Vai al sito AFANT</a></td></tr></table>'
        . '</td></tr>';
    $piede = 'A.FA.N.T. Arezzo · Via Newton 48, 52100 Arezzo · <a href="mailto:info@afantarezzo.it" style="color:#1f5fa8">info@afantarezzo.it</a><br>'
        . 'Ricevi questa email perché hai inviato una richiesta dal sito. Informativa privacy: <a href="https://www.afantarezzo.it/privacy/" style="color:#1f5fa8">afantarezzo.it/privacy</a>';
    $html = email_involucro('Richiesta ricevuta', 'A.FA.N.T. Arezzo', $corpo, $piede);
    $testo = "Buongiorno,\n\nabbiamo ricevuto la tua richiesta. Ti risponderemo appena possibile.\nRiferimento: $id\n\nA.FA.N.T. Arezzo\ninfo@afantarezzo.it";
    return [$html, $testo];
}

function email_webmaster(array $data, string $id, DateTimeImmutable $now, array $allegati, string $testoSemplice): array {
    $iscr = ($data['tipo'] ?? '') === 'iscrizione';
    $anno = (string)($data['anno_iscrizione'] ?? $now->format('Y'));
    $titolo = $iscr ? 'Nuova iscrizione ' . $anno : 'Nuova richiesta dal sito';
    $chip = '';
    if ($iscr) {
        $si = ($data['iscritto_precedente'] ?? '') === 'si';
        $colore = $si ? '#1f5fa8' : '#2e8b3e';
        $etich = $si ? 'Già iscritto l\'anno precedente' : 'Nuova iscrizione';
        $chip = '<table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:' . $colore . ';color:#ffffff;border-radius:999px;padding:7px 14px;font-size:13px;font-weight:bold">' . $etich . '</td>'
            . '<td style="padding-left:12px;font-size:13px;color:#5a6b80">Riferimento ' . email_e($id) . ' · ' . email_e($now->format('d/m/Y H:i')) . '</td></tr></table>';
    } else {
        $chip = '<div style="font-size:13px;color:#5a6b80">Riferimento ' . email_e($id) . ' · ' . email_e($now->format('d/m/Y H:i')) . '</div>';
    }
    $etichette = ['nome'=>'Nome','cognome'=>'Cognome','codice_fiscale'=>'Codice fiscale','causale'=>'Causale','iscritto_precedente'=>'Iscritto anno precedente','email'=>'Email','pec'=>'PEC','telefono'=>'Telefono','indirizzo'=>'Indirizzo','localita'=>'Località','provincia'=>'Provincia o regione','cap'=>'CAP','paese'=>'Paese','luogo_nascita'=>'Luogo di nascita','ordine'=>'Ordine di appartenenza','luogo_lavoro'=>'Luogo di lavoro','pagamento'=>'Metodo di pagamento','note'=>'Note','oggetto'=>'Oggetto','messaggio'=>'Messaggio'];
    $righe = '';
    foreach ($etichette as $k => $lab) {
        if (!isset($data[$k]) || $data[$k] === '') continue;
        $v = $k === 'iscritto_precedente' ? ($data[$k] === 'si' ? 'Sì' : 'No') : $data[$k];
        $righe .= '<tr><td style="padding:9px 0;border-bottom:1px solid #e4ebf3;width:38%;color:#5a6b80;vertical-align:top">' . email_e($lab) . '</td>'
            . '<td style="padding:9px 0;border-bottom:1px solid #e4ebf3">' . nl2br(email_e($v)) . '</td></tr>';
    }
    $all = $allegati ? implode(', ', array_map('email_e', $allegati)) : 'nessuno';
    $corpo = '<tr><td style="padding:16px 28px 0">' . $chip . '</td></tr>'
        . '<tr><td style="padding:16px 28px 8px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.5">' . $righe . '</table></td></tr>'
        . '<tr><td style="padding:12px 28px 24px;font-size:13px;color:#5a6b80;line-height:1.6">Allegati: ' . $all . ($iscr ? '<br>Il registro aggiornato (Excel) è in allegato a questa email.' : '') . '</td></tr>';
    $piede = 'Email automatica dal sito. Per rispondere al richiedente usa Rispondi: l\'indirizzo è già impostato.';
    $html = email_involucro($titolo, 'Sito A.FA.N.T. Arezzo · avviso automatico', $corpo, $piede);
    return [$html, $testoSemplice];
}
