# AFANT Arezzo — guida per continuare e recuperare il sito

**Situazione fotografata il 3 novembre 2026.** Il nuovo sito è pubblicato su https://www.afantarezzo.it/. Il sottodominio https://nuovo.afantarezzo.it/ è una copia di prova. Questa guida e il codice nell'archivio sono una copia locale: il server e i suoi dati possono cambiare in seguito.

## Cosa contiene questo archivio

- `progetto/`: sorgenti del sito Astro, pagine, notizie, dati, immagini, PDF effettivamente usati, strumenti SEO, codice PHP dei moduli e librerie PHP.
- `sito-pronto/`: copia generata per il dominio principale, con sitemap, file PHP pubblici e copia del `.htaccess` predisposto per il server attuale. Contiene anche `api/.htaccess`, importante per l'accesso al registro. Non equivale a un backup del server completo e non va caricata alla cieca.
- `materiali/`: materiali di lavoro presenti in locale.
- `AVVIA-SITO.command`: avvio dell'anteprima su macOS, dopo l'installazione delle dipendenze.
- `RECUPERO.md`, `progetto/docs/` e `progetto/CLAUDE.md`: note precedenti, inventari e regole di progetto; alcune descrivono fasi superate. In caso di differenza sullo stato, questa guida è più recente, ma va sempre confrontata con il server.

Non sono inclusi i file originali ZIP ricevuti dal cliente, la cronologia Git e `node_modules`; quest'ultimo si reinstalla come indicato sotto. **Non sono inclusi password, chiavi segrete, `config.php` reale, database degli iscritti, allegati personali, caselle email o backup del vecchio sito.** Il contenuto dell'archivio non comprende tutti i dati operativi e non garantisce, da solo, un ripristino completo.

## Avviare il progetto su un altro computer

1. Estrai l'archivio in una cartella sul computer.
2. Installa Node.js versione **22.23.4 o superiore** e npm 9.6.5 o superiore. Il progetto usa Astro 7.4.4; `package-lock.json` fissa le dipendenze.
3. Apri un terminale nella cartella `progetto` ed esegui:

```sh
npm ci
npm run dev -- --host 127.0.0.1
```

4. Apri nel browser l'indirizzo locale mostrato nel terminale. In alternativa, su macOS puoi aprire `AVVIA-SITO.command` dopo `npm ci`.

L'installazione `npm ci` richiede accesso ai pacchetti npm. L'anteprima Astro è statica: **non invia email, non esegue PHP e non gestisce il registro**. I test reali richiedono PHP e un ambiente protetto. Non usare dati personali reali in un ambiente di prova non protetto.

## Dove modificare i contenuti

- **Pagine:** `progetto/src/pages/` (per esempio `contatti.astro`, `iscrizione.astro`, `privacy.astro`, `documenti.astro`). L'aspetto comune è in `src/layouts/`, `src/components/` e `src/styles/`.
- **Notizie:** un file Markdown per articolo in `progetto/src/content/news/`. Copia la struttura di un articolo esistente, assegna uno slug univoco e controlla titolo, data, categoria e testo.
- **Evento in evidenza in home:** `progetto/src/data/eventi.json`. La scelta dell'evento avviene al momento della generazione statica. Dopo il convegno del 5 novembre 2026 rigenerare e ripubblicare la home, verificando che non venga ancora indicato come futuro.
- **Documenti e allegati:** `progetto/src/data/documenti.json`, `src/data/allegati.json` e file in `progetto/public/documenti/`. Assicurarsi che ogni percorso punti a un file effettivamente presente e pubblicabile.
- **Immagini, loghi e file pubblici:** `progetto/public/`. Le locandine degli eventi sono in `public/locandine/`.
- **Indirizzi dei vecchi contenuti:** `progetto/src/data/redirects.json` e `redirects-pagine.json`. Il generatore produce anche il frammento `progetto/docs/redirect-301.conf`. La pagina storica `/codice-deontologico/` non ha ancora una destinazione verificata.

Non inserire nel progetto pubblico curricula o altri dati personali senza una decisione documentata sui diritti di pubblicazione e sulla privacy. Non mettere password o dati degli iscritti nelle pagine, in `public/`, nei repository o negli ZIP condivisi.

## Generare il sito dopo una modifica

Nella cartella `progetto`, su macOS/Linux:

```sh
PUBLIC_FORMS_ENABLED=1 PUBLIC_TURNSTILE_SITEKEY=0x4AAAAAAFxvf9xc30R8n2pH npm run build:produzione
PUBLIC_FORMS_ENABLED=1 PUBLIC_TURNSTILE_SITEKEY=0x4AAAAAAFxvf9xc30R8n2pH npm run build:prova
```

- `dist-produzione/` contiene pagine per **www.afantarezzo.it**, con canonical, sitemap.xml e robots.txt.
- `dist-prova/` è per **nuovo.afantarezzo.it**, con `noindex` sulle pagine HTML. Non trasferire la build di prova sul dominio principale.
- Il valore `PUBLIC_TURNSTILE_SITEKEY` è una **chiave pubblica**, riferita al widget in uso alla data indicata. Verificare il widget e gli hostname prima di cambiarla. La **chiave segreta** non va qui e non va pubblicata.
- Questi comandi generano il sito statico; i punti di ingresso PHP e il servizio privato sono in `servizio-moduli/` e richiedono configurazione sul server.
- `npm run build:anteprima` abilita le precedenti note interne arancioni: non usarlo per pubblicare.

Dopo ogni modifica controllare l'anteprima, rigenerare la variante corretta, verificare link e file coinvolti, conservare una copia dei file online da sostituire, poi caricare solo le modifiche necessarie attraverso cPanel. Non sostituire mai `.htaccess`, `config.php`, cartelle private o archivi dati senza prima confrontarli e averne un backup recuperabile. Verificare online il risultato, anche da mobile se la grafica è cambiata.

## Dominio principale, cPanel e moduli

- La radice web del dominio principale è `/home/afantarezzo/public_html`. PHP 8.3 è stato selezionato in MultiPHP Manager. Il dominio senza `www` è stato configurato per reindirizzare a `https://www.afantarezzo.it/`.
- La cartella del sito di prova è `/home/afantarezzo/nuovo.afantarezzo.it`. La build di prova non va scambiata con quella definitiva.
- Il servizio PHP riservato è in `/home/afantarezzo/afant-privato`, fuori dalla radice web. `app/` contiene il codice, `vendor/` le librerie PHP e `dati/` il database e gli allegati; `config.php` contiene impostazioni e segreti. Percorsi e permessi sono specifici del server attuale e vanno verificati in caso di migrazione.
- Gli accessi pubblici sono `api/invia.php` e `api/registro.php`; puntano al servizio privato. Il registro è protetto da credenziali amministrative e fornisce un file Excel `.xlsx`.
- SMTP: `mail.afantarezzo.it`, porta 465 e TLS implicito; invio da `noreply@afantarezzo.it` a `info@afantarezzo.it`. Le risposte alle ricevute vanno a `info@afantarezzo.it`. La password è conservata nel `config.php` effettivo sul server, non in questo archivio.
- Nella configurazione di produzione `site_host` deve corrispondere al dominio effettivamente usato, `www.afantarezzo.it`. La configurazione è condivisa con la prova: cambiare questo valore per uno dei due ambienti può bloccare gli invii sull'altro. Organizzare ambienti separati prima di utilizzare entrambi in parallelo.
- La configurazione del registro, l'account amministrativo e la chiave segreta Turnstile sono riservati. Non trascriverli in chat o file pubblici. La rotazione della password SMTP e della chiave Turnstile è stata effettuata dopo la pubblicazione; in caso di ulteriore rotazione aggiornare subito il file privato e controllare gli invii.
- Il `.htaccess` di `sito-pronto/` riflette il file preparato per la pubblicazione: include il gestore PHP del provider, il reindirizzamento al `www` e regole 301. Non assumere che sia ancora identico a quello sul server in futuro. Prima di usarlo confrontare e preservare ogni direttiva esistente, certificato, handler e regola del provider. `api/.htaccess` contiene la direttiva per l'autenticazione del registro.
- La sitemap prevista è `https://www.afantarezzo.it/sitemap.xml`. Una sitemap o un canonical non garantiscono presenza o posizione su Google. Dopo cambi importanti verificare sitemap, vecchie URL e Search Console.

## Backup e ripristino

Questo ZIP va conservato in un luogo privato e accessibile anche da un altro computer. Per poter ripristinare davvero il servizio occorrono **in aggiunta**, in copie separate e protette, backup verificati di:

1. File pubblici effettivamente in uso, `.htaccess` inclusi, e file privati dell'applicazione.
2. `config.php` e credenziali, custoditi con accesso limitato o in un gestore password. Non renderli scaricabili dal web.
3. Database SQLite `registro.sqlite` e cartella allegati dalla posizione privata realmente configurata, con una procedura che garantisca una copia coerente del database anche se è in uso; chiedere al provider se necessario.
4. Caselle email, altri dati operativi e i backup del vecchio sito/database se sono ancora necessari.

Definire frequenza, responsabili, durata e prova periodica di ripristino. Non considerare questo ZIP la copia del registro o la prova che esista un backup del vecchio WordPress. Prima di sostituire il sito, verificare concretamente da dove e come si ripristina ogni componente. Non pubblicare backup o archivi riservati in `public_html`.

## Stato delle verifiche e privacy

Al 3 novembre 2026: il sito principale risponde; il modulo contatti ha mostrato conferma e la ricezione della richiesta e della ricevuta è stata confermata. Dopo la sostituzione della chiave Turnstile, un test ha avuto esito positivo **durante il periodo di sovrapposizione delle chiavi**: ripetere la verifica dopo la scadenza del periodo, circa due ore dalla rotazione, per escludere l'uso della chiave precedente.

Il modulo iscrizione sul dominio principale, la ricezione degli allegati e il registro Excel sul dominio definitivo richiedono una prova completa. Le richieste di prova eventualmente registrate non sono iscrizioni reali: identificarle e gestirle senza cancellare dati reali.

L'informativa privacy e cookie richiede ancora la verifica con i fornitori e un professionista. Le procedure di cancellazione e i tempi di backup/log devono essere definiti e applicati; la cancellazione non è automatica. I tempi proposti sono 12 mesi dalla chiusura per contatti e richieste di iscrizione rifiutate o incomplete, e durata del rapporto più 10 anni per i dati necessari agli iscritti, fatte salve verifiche legali e casi specifici. La pubblicazione del sito non equivale a una certificazione di conformità GDPR.
