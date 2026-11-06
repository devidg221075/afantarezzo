# Servizio moduli AFANT — predisposizione per la prova

Stato: codice preparato localmente; NON installato e NON attivo sulla VPS. Non usare per dati reali finché non sono stati completati configurazione, prove e informativa privacy.

## Componenti

- `app/` e `vendor/`: da collocare in `/home/afantarezzo/afant-privato/`, fuori dalle directory pubbliche. La cartella `dati/` deve essere scrivibile dal processo PHP e non accessibile dal web.
- `public/api/`: contiene i due punti di accesso da collocare sotto la sola radice web del sottodominio. I percorsi nei file puntano a `/home/afantarezzo/afant-privato/app/`.
- Configurazione: copiare `app/config.example.php` come `config.php` nella cartella privata. Lasciare `enabled` a false fino al termine delle verifiche. Non inserire password o chiavi in archivi, sorgenti, chat o directory pubbliche.
- Il sito deve essere generato con `PUBLIC_FORMS_ENABLED=1` e `PUBLIC_TURNSTILE_SITEKEY` solo dopo aver predisposto un widget Cloudflare Turnstile per il dominio. La site key è pubblica; la secret key resta esclusivamente nel file privato. Il server verifica token, dominio e azione prima di elaborare il modulo.
- SMTP: server `mail.afantarezzo.it`, porta 465, TLS implicito da verificare; nome utente indicato `info@afantarezzo.it` (da verificare); usare una casella autenticata e un mittente approvato dal servizio. Destinatario `info@afantarezzo.it`.
- Registro: l'esportazione `/api/registro.php` richiede HTTPS e credenziali amministrative; nel file privato va impostato un hash generato con `password_hash`, non la password in chiaro. Richiede SQLite, cURL, fileinfo, mbstring, OpenSSL e le estensioni richieste da PhpSpreadsheet (inclusi zip, xml e gd). Confermare la disponibilità su PHP 8.3 della VPS.
- Invio: CAPTCHA verificato lato server; PDF, JPG e PNG, massimo 3 file da 5 MB ciascuno; i limiti effettivi possono dipendere dalla configurazione PHP. Le richieste di iscrizione e le ricevute sono salvate nell'archivio privato; il contatto è inviato per email. L'email di conferma non ripete dati sensibili. Un fallimento nella notifica dell'iscrizione segnala che la richiesta è stata registrata, con riferimento da comunicare ad AFANT.

## Verifiche prima dell'attivazione

1. Verificare che la cartella privata, SQLite, allegati e file di configurazione non siano accessibili via URL; verificare permessi, backup e capacità di ripristino.
2. Completare l'informativa privacy in base ai servizi reali, ai tempi di conservazione, agli accessi e agli allegati; valutare anche Cloudflare Turnstile.
3. Con credenziali di prova e chiavi adeguate all'ambiente, provare: validazione dei campi, invio reale alla casella AFANT, ricevuta al mittente, allegati validi e rifiutati, token CAPTCHA invalido, errore SMTP, registro protetto ed esportazione Excel. Eliminare i soli dati di prova con procedura concordata.
4. Solo dopo questi controlli configurare le chiavi definitive e abilitare i moduli; prima verificare anche le condizioni di pubblicazione dei materiali destinati agli iscritti.

Prove locali svolte: sintassi PHP, validazione campi, rifiuto della configurazione assente, esportazione Excel con contenuto trattato come testo, build Astro in modalità disattivata e compilazione condizionata del widget. Non sono stati verificati invio SMTP, chiamate Turnstile, comportamento del server, né recapito delle email.

## Widget Turnstile creato

Widget `AFANT - prova`, modalità Managed. La site key pubblica da usare per la build è `0x4AAAAAAFxvf9xc30R8n2pH`. Host associati: `afantarezzo.it` e `nuovo.afantarezzo.it`. La secret key deve essere inserita direttamente nel file di configurazione privato sulla VPS e non è riportata qui.
