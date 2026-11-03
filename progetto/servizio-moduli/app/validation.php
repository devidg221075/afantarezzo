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
function email_field(array $post, string $key, string $confirm): string {
    $a = field($post, $key, 254); $b = field($post, $confirm, 254);
    if (!filter_var($a, FILTER_VALIDATE_EMAIL) || strcasecmp($a, $b) !== 0 || str_contains($a, "\n")) throw new InvalidArgumentException('Verifica che gli indirizzi email siano validi e coincidano.');
    return $a;
}
function validate_form(array $post): array {
    $type = field($post, 'tipo', 20);
    if (!in_array($type, ['contatto', 'iscrizione'], true)) throw new InvalidArgumentException('Modulo non valido.');
    $data = ['tipo' => $type, 'nome' => field($post, 'nome', 100), 'cognome' => field($post, 'cognome', 100), 'email' => email_field($post, 'email', 'conferma_email')];
    if (($post['privacy'] ?? '') !== 'on') throw new InvalidArgumentException('Conferma di aver letto l’informativa privacy.');
    if ($type === 'contatto') {
        $data += ['telefono' => field($post, 'telefono', 40, false), 'oggetto' => field($post, 'oggetto', 180), 'messaggio' => field($post, 'messaggio', 5000)];
    } else {
        foreach (['codice_fiscale'=>20,'causale'=>200,'telefono'=>40,'indirizzo'=>250,'localita'=>120,'provincia'=>120,'cap'=>20,'paese'=>100,'luogo_nascita'=>120,'data_nascita'=>10,'iscritto_precedente'=>3,'pagamento'=>20] as $key=>$max) $data[$key] = field($post, $key, $max);
        foreach (['ordine'=>150,'luogo_lavoro'=>200,'note'=>5000] as $key=>$max) $data[$key] = field($post, $key, $max, false);
        $data['pec'] = email_field($post, 'pec', 'conferma_pec');
        if (!in_array($data['iscritto_precedente'], ['si','no'], true) || !in_array($data['pagamento'], ['bonifico','contante'], true)) throw new InvalidArgumentException('Seleziona iscrizione precedente e modalità di pagamento.');
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $data['data_nascita']);
        if (!$birth || $birth->format('Y-m-d') !== $data['data_nascita'] || $birth > new DateTimeImmutable('today')) throw new InvalidArgumentException('Controlla la data di nascita.');
        $data['codice_fiscale'] = strtoupper($data['codice_fiscale']);
        if (!preg_match('/^[A-Z0-9]{16}$/', $data['codice_fiscale'])) throw new InvalidArgumentException('Controlla il codice fiscale.');
    }
    return $data;
}
