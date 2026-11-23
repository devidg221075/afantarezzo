<?php
declare(strict_types=1);

function clean_text(mixed $value, int $max, bool $required = false): string {
    if (!is_string($value)) throw new InvalidArgumentException('Campo non valido.');
    $value = trim(str_replace(["\r\n", "\r"], "\n", $value));
    if (($required && $value === '') || mb_strlen($value, 'UTF-8') > $max || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) throw new InvalidArgumentException('Controlla i campi obbligatori e la loro lunghezza.');
    return $value;
}
function field(array $post, string $key, int $max, bool $required = true): string {
    return clean_text($post[$key] ?? '', $max, $required);
}
function email_field(array $post, string $key, string $confirm, bool $needConfirm = true): string {
    $a = field($post, $key, 254); $b = $needConfirm ? field($post, $confirm, 254) : $a;
    if (!filter_var($a, FILTER_VALIDATE_EMAIL) || strcasecmp($a, $b) !== 0 || str_contains($a, "\n")) throw new InvalidArgumentException('Verifica che gli indirizzi email siano validi e coincidano.');
    return $a;
}
function validate_form(array $post): array {
    $type = field($post, 'tipo', 20);
    if (!in_array($type, ['contatto', 'iscrizione'], true)) throw new InvalidArgumentException('Modulo non valido.');
    $data = ['tipo' => $type, 'nome' => field($post, 'nome', 100), 'cognome' => field($post, 'cognome', 100), 'email' => ($type === 'iscrizione' && trim((string)($post['email'] ?? '')) === '') ? '' : email_field($post, 'email', 'conferma_email', $type === 'contatto' || array_key_exists('conferma_email', $post))];
    if (($post['privacy'] ?? '') !== 'on') throw new InvalidArgumentException('Conferma di aver letto l’informativa privacy.');
    if ($type === 'contatto') {
        $data += ['telefono' => field($post, 'telefono', 40, false), 'oggetto' => field($post, 'oggetto', 180), 'messaggio' => field($post, 'messaggio', 5000)];
    } else {
        $gia = (($post['iscritto_precedente'] ?? '') === 'si');
        foreach (['codice_fiscale'=>20,'iscritto_precedente'=>3,'pagamento'=>20] as $key=>$max) $data[$key] = field($post, $key, $max);
        foreach (['causale'=>200,'telefono'=>40] as $key=>$max) $data[$key] = field($post, $key, $max, false);
        foreach (['indirizzo'=>250,'localita'=>120,'provincia'=>120,'cap'=>20,'paese'=>100,'luogo_nascita'=>120] as $key=>$max) $data[$key] = field($post, $key, $max, false);
        $data['data_nascita'] = field($post, 'data_nascita', 10, false);
        foreach (['ordine'=>150,'luogo_lavoro'=>200,'note'=>5000] as $key=>$max) $data[$key] = field($post, $key, $max, false);
        $data['pec'] = (trim((string)($post['pec'] ?? '')) === '' && trim((string)($post['conferma_pec'] ?? '')) === '') ? '' : email_field($post, 'pec', 'conferma_pec');
        $current = (int)(new DateTimeImmutable('now', new DateTimeZone('Europe/Rome')))->format('Y');
        $year = filter_var($post['anno_iscrizione'] ?? $current, FILTER_VALIDATE_INT);
        if ($year === false || $year < $current - 1 || $year > $current + 1) throw new InvalidArgumentException('Anno di iscrizione non valido.');
        $data['anno_iscrizione'] = (string)$year;
        if (!in_array($data['iscritto_precedente'], ['si','no'], true) || !in_array($data['pagamento'], ['bonifico','contante'], true)) throw new InvalidArgumentException('Seleziona iscrizione precedente e modalità di pagamento.');
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $data['data_nascita']);
        if ($data['data_nascita'] !== '' && (!$birth || $birth->format('Y-m-d') !== $data['data_nascita'] || $birth > new DateTimeImmutable('today'))) throw new InvalidArgumentException('Controlla la data di nascita.');
        $data['codice_fiscale'] = strtoupper($data['codice_fiscale']);
        if (!preg_match('/^[A-Z0-9]{16}$/', $data['codice_fiscale'])) throw new InvalidArgumentException('Controlla il codice fiscale.');
    }
    return $data;
}

// Motivo del rifiuto della verifica antispam, per il registro errori del server.
// Contiene solo codici e nomi di dominio/azione: nessun dato personale, nessun token.
function captcha_diagnosi(mixed $json, int $status, string $curlError, string $host, string $action): string {
    if ($curlError !== '') return 'connessione a Cloudflare non riuscita: ' . substr(preg_replace('/[^A-Za-z0-9 .:_-]/', '', $curlError), 0, 120);
    if ($status !== 200 || !is_array($json)) return 'risposta non valida di Cloudflare, stato HTTP ' . $status;
    if (($json['success'] ?? false) !== true) {
        $codes = array_filter((array)($json['error-codes'] ?? []), 'is_string');
        return 'rifiutato da Cloudflare: ' . ($codes ? implode(',', array_map(fn($c) => preg_replace('/[^a-z0-9-]/i', '', $c), array_slice($codes, 0, 5))) : 'nessun codice');
    }
    if (($json['hostname'] ?? '') !== $host) return 'dominio diverso: atteso ' . $host . ', ricevuto ' . substr(preg_replace('/[^A-Za-z0-9.-]/', '', (string)($json['hostname'] ?? '')), 0, 80);
    if (($json['action'] ?? '') !== $action) return 'azione diversa: attesa ' . $action . ', ricevuta ' . substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)($json['action'] ?? '')), 0, 40);
    return 'valido';
}
