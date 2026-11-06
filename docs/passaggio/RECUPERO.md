# A.FA.N.T. Arezzo — riprendere il progetto

**Nota storica:** le sezioni seguenti descrivono la preparazione prima della pubblicazione. Per lo stato e le istruzioni aggiornate leggere prima `INIZIA-DA-QUI.md`.

**Copia preparata il 3 novembre 2026.** Questo documento è il punto di partenza anche per una nuova conversazione con un assistente. Il sito di prova è https://nuovo.afantarezzo.it/; il sito definitivo previsto è https://www.afantarezzo.it/. Questa copia non trasferisce il sito definitivo e non modifica il server.

## Contenuto dello ZIP

- `progetto/`: sorgenti Astro, articoli, pagine, grafica e documenti utilizzati, strumenti, mappature SEO, codice PHP dei moduli e librerie PHP in `servizio-moduli/vendor/`.
- `sito-pronto/`: copia statica generata per il **dominio definitivo**, comprensiva di sitemap e robots.txt, più i punti di accesso PHP in `api/`. È una preparazione tecnica, non una versione già pubblicata sul dominio principale. Per attivare i moduli occorrono anche servizio privato e configurazione descritti sotto.
- `materiali/`: materiali locali di lavoro raccolti per la grafica.
- `AVVIA-SITO.command`: avvio dell'anteprima su macOS dopo aver installato le dipendenze.

L'archivio contiene i file utilizzati dal progetto, non tutti gli ZIP originali forniti in precedenza né una copia della cronologia Git. Non include `node_modules`: si ricrea dal file `package-lock.json` tramite `npm ci`, con una connessione Internet. Le librerie PHP sono incluse. Non vengono inclusi credenziali, file di configurazione reale, registro, allegati personali e curricula eventualmente conservati sul server.

## Primo avvio su un altro computer

1. Estrai tutto lo ZIP in una cartella locale. Installa Node.js **22.23.4 o successivo**, con npm 9.6.5 o successivo, compatibile con Astro 7.
2. Apri Terminale nella cartella `progetto` estratta e avvia:

```sh
npm ci
npm run dev -- --host 127.0.0.1
```

3. Apri l'indirizzo locale mostrato dal terminale. Su macOS, dopo `npm ci`, puoi usare anche `AVVIA-SITO.command` nella cartella principale. Se macOS impedisce l'apertura, aprilo dal menu contestuale o usa i comandi del punto 2.
4. L'anteprima Astro mostra le pagine, ma **non esegue il servizio PHP**: i moduli non inviano email e il registro non funziona in questo modo. Per testarli serve un ambiente PHP e una configurazione dedicata. Non usare dati reali in test non protetti.

## Modificare e generare il sito

- Pagine: `progetto/src/pages/`; notizie: `progetto/src/content/news/`; evento in evidenza: `progetto/src/data/eventi.json`.
- Documenti collegati: `progetto/src/data/documenti.json`, `progetto/src/data/allegati.json` e file in `progetto/public/documenti/`. Altre risorse usate dal sito sono in `progetto/public/`.
- La notizia del convegno CONASFA e la locandina sono incluse. L'evento in home viene scelto durante la generazione: dopo la data dell'evento occorre rigenerare e pubblicare le pagine per aggiornare l'evidenza.
- SEO e istruzioni: `progetto/docs/seo-pubblicazione.md`, `progetto/docs/redirect-301.conf` e i due file `progetto/src/data/redirects*.json`.
- I vecchi documenti `README.md`, `CLAUDE.md` e `servizio-moduli/CONFIGURAZIONE.md` possono descrivere fasi precedenti: per lo stato attuale prevale questo documento e occorre comunque verificare il server.

Per ricostruire le pagine con i moduli visibili, da `progetto` su macOS/Linux:

```sh
PUBLIC_FORMS_ENABLED=1 PUBLIC_TURNSTILE_SITEKEY=0x4AAAAAAFxvf9xc30R8n2pH npm run build:prova
PUBLIC_FORMS_ENABLED=1 PUBLIC_TURNSTILE_SITEKEY=0x4AAAAAAFxvf9xc30R8n2pH npm run build:produzione
```

La prima genera `dist-prova/` per il sottodominio con `noindex`; la seconda genera `dist-produzione/` per www, con sitemap e canonical. **Non usare la build di prova sul dominio definitivo**. La chiave sopra è la site key pubblica del widget di prova; controllare in Cloudflare quale widget e hostname si useranno in produzione. La secret key non va mai nei sorgenti, nei file pubblici o nello ZIP. Su Windows utilizzare un terminale compatibile con la sintassi mostrata oppure impostare le variabili d'ambiente in modo equivalente.

## Come funziona il server

- PHP di prova: 8.3, tramite cPanel; senza SSH. La struttura usata sul server di prova prevede la cartella web `/home/afantarezzo/nuovo.afantarezzo.it` e la cartella **privata** `/home/afantarezzo/afant-privato`, fuori dalla radice pubblica.
- `progetto/servizio-moduli/app/` e `vendor/` sono la copia locale del servizio. Verificare le differenze rispetto ai file effettivamente installati sul server prima di sostituirli: durante le prove possono essere state effettuate modifiche direttamente in cPanel. Non sovrascrivere dati, configurazioni o codice attivo alla cieca.
- `progetto/servizio-moduli/public/api/` contiene gli accessi pubblici a invio e registro. I percorsi assoluti PHP sono specifici del server attuale; `api/.htaccess` contiene `CGIPassAuth On` per l'autenticazione del registro. Verificare che il provider consenta tale direttiva.
- Configurazione vera: `/home/afantarezzo/afant-privato/config.php`, **esclusa da questo archivio**. `app/config.example.php` è solo uno schema con valori vuoti e servizio disabilitato. I dati del registro e gli allegati si trovano nella cartella privata configurata, non in questo ZIP.
- SMTP: `mail.afantarezzo.it`, porta 465 con TLS implicito; mittente `noreply@afantarezzo.it`, destinatario e indirizzo di risposta delle ricevute `info@afantarezzo.it`. Password, chiave segreta Turnstile e credenziali del registro devono essere conservate e reinserite privatamente, mai in chat o nella cartella web.
- Il codice verifica il nome host previsto (`site_host`) e l'origine della richiesta: passando da `nuovo.afantarezzo.it` a `www.afantarezzo.it` non basta copiare le pagine. Se prova e produzione condividono configurazione e archivio, modificare `site_host` può interrompere i moduli di prova. Pianificare la separazione degli ambienti o il passaggio coordinato.
- Il registro si esporta in Excel tramite `/api/registro.php` con credenziali amministrative. Non confondere le righe di prova con richieste vere. Gli allegati non sono ancora stati verificati integralmente con un invio reale.

## Backup reale e trasferimento sul dominio principale

Questo ZIP **non è un backup completo dei dati operativi**. Occorre un backup riservato e verificato del sito attuale, delle configurazioni private, del database SQLite delle iscrizioni, degli allegati, della posta, dei backup e di eventuali altri database/file attualmente utilizzati. Conservare queste copie separatamente e proteggerle; non inserirle in una cartella accessibile pubblicamente. Le password si conservano in un gestore dedicato.

Prima della pubblicazione definitiva: verificare PHP ed estensioni, HTTPS e host Turnstile, percorsi privati e permessi, SMTP, CAPTCHA, invio dei due moduli, ricevuta al mittente, invio e ricezione allegati ed esportazione registro. Integrare i 301 nel `.htaccess` esistente solo dopo averlo salvato e verificato; non sostituire la configurazione esistente senza esaminarla. Provare i redirect effettivi e inviare la sitemap a Search Console. Il vecchio URL `/codice-deontologico/` non ha una destinazione verificata. I vecchi contenuti e documenti non presenti vanno trattati con cautela.

## Privacy e stato delle verifiche

L'informativa pubblicata sul sito di prova contiene ancora aspetti da verificare. In particolare, le procedure effettive di cancellazione non sono automatiche; occorre chiarire responsabilità, tempi per backup e log, fornitori/trasferimenti e il trattamento dei dati di iscrizione. I periodi ipotizzati sono 12 mesi dalla chiusura per contatti e iscrizioni rifiutate o incomplete, e durata del rapporto più 10 anni per i dati necessari agli iscritti, da verificare e applicare. Serve revisione da parte di un professionista privacy. Il sottodominio di prova è pubblico e può ricevere dati reali.
