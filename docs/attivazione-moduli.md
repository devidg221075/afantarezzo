# Attivare in sicurezza i moduli contatti e iscrizione 2027

Guida passo passo. I moduli oggi sono spenti: il sito mostra "Invio non ancora attivo". Si attivano solo dopo aver installato il servizio sul server e aver fatto le prove.

## Cosa serve
- Accesso al server Serverplan (pannello, SFTP o SSH) e PHP 8.3 con SQLite, cURL, fileinfo, mbstring, OpenSSL, zip, xml, gd; Composer (o ZIP con la cartella `vendor/` già pronta).
- Una casella di posta autenticata per l'invio (host `mail.afantarezzo.it`, porta 465) e la sua password.
- La chiave segreta di Cloudflare Turnstile (la chiave pubblica è già nel sito: `0x4AAAAAAFxvf9xc30R8n2pH`; il widget deve avere tra gli host `afantarezzo.it`, che copre anche `www`).
- Il file storico `archivio-iscritti-storico.xls` (resta nella cartella privata, mai nel sito).

## 1. Cartella privata (fuori dal sito)
1. Crea `/home/afantarezzo/afant-privato/` e dentro `app/`, `dati/`.
2. Carica il contenuto di `app/` da `3-servizio-moduli-server.zip` in `afant-privato/app/`.
3. Nella cartella `afant-privato/` esegui `composer install --no-dev` (crea `vendor/`).
4. Copia `archivio-iscritti-storico.xls` in `afant-privato/dati/`.
5. Permessi: `dati/` 0700, file dentro 0600. La cartella `afant-privato/` non deve stare dentro la radice web.

## 2. Punti di accesso nel sito
Copia `public/api/invia.php` e `public/api/registro.php` nella radice web, cartella `api/`.

## 3. Configurazione
1. In `afant-privato/` copia `app/config.example.php` come `config.php` e compilalo:
   - `site_host`: **`www.afantarezzo.it`** (l'esempio ha `nuovo.afantarezzo.it`: va cambiato, serve al controllo dell'origine e di Turnstile);
   - posta: `smtp_user`, `smtp_password`, `from_email`; destinatario `recipient`;
   - `turnstile_secret`: la chiave segreta (solo qui, mai in chat né nel sito);
   - `admin_user` e `admin_password_hash`: l'hash si crea sul tuo computer con `crea-hash-locale.php`, **non** la password in chiaro;
   - `allega_registro`: `true` (scelta del cliente): a ogni nuova iscrizione l'email arriva con il file Excel aggiornato in allegato. Il file contiene i dati di tutti gli iscritti: la casella `recipient` deve avere password robusta, accesso solo al Consiglio Direttivo e, se possibile, verifica in due passaggi;
   - `enabled`: lascialo `false` finché non finiscono i punti 4 e 5.
2. Permessi di `config.php`: 0600.

## 4. Sicurezza: controlli prima di accendere
- Apri nel browser `https://www.afantarezzo.it/afant-privato/`, `.../dati/registro.sqlite`, `.../config.php`: devono dare errore, mai scaricare file.
- `https://www.afantarezzo.it/api/registro.php` deve chiedere utente e password.
- Il sito deve rispondere solo in HTTPS.
- Backup giornaliero attivo e include `afant-privato/dati/` (Serverplan, 1 anno).

## 5. Accensione
1. Metti `enabled` a `true` in `config.php`.
2. Pubblica la build con i moduli attivi (te la preparo io: ZIP con la chiave Turnstile).

## 6. Prove (con dati finti, poi cancellati)
- Modulo contatti: invio normale; email arrivata al Consiglio Direttivo; ricevuta al mittente.
- Modulo iscrizione 2027: invio con ricevuta PDF; allegato non valido rifiutato; la causale è "Iscrizione Afant 2027".
- Controlla che il file `afant-privato/dati/iscrizioni-afant-aggiornato.xlsx` compaia e abbia la nuova riga, con i fogli storici.
- Scarica il registro da `/api/registro.php` e controlla che coincida.
- Prova di errore: password sbagliata sul registro (401); troppi invii (limite 12 all'ora).
- Cancella le richieste di prova dal registro e dagli allegati.

## 7. Dopo l'accensione
- Il file Excel si aggiorna da solo a ogni nuova iscrizione (in `afant-privato/dati/`), con una copia della versione precedente.
- Scaricarlo in qualsiasi momento: `https://www.afantarezzo.it/api/registro.php` (utente e password).
- Ricorda la regola dell'informativa: cancellazione dei dati alla scadenza (promemoria annuale).
