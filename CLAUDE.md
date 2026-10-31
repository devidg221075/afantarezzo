# Regole di lavoro (A.FA.N.T. Arezzo, rifacimento del sito)

Le regole del cliente valgono per questo sito e per qualsiasi altro sito sviluppato con lui.

## Come lavorare con il cliente
- Rispondere in italiano.
- Fare **una domanda alla volta**, suggerendo le risposte (la consigliata per prima).
- Il cliente guarda le pagine nel browser e annota: mostrare sempre il risultato con schermate o con una copia sfogliabile prima di dare un lavoro per concluso.

## Contenuti del sito vecchio (regola fissa)
- Prima di progettare o riordinare qualsiasi pagina, **leggere con attenzione tutti i contenuti del sito vecchio**: pagine, articoli, eventi, download, moduli, testi del piè di pagina e delle pagine di servizio.
- **Nessun contenuto del sito vecchio va perso**: va riportato nel sito nuovo, anche migliorandone disposizione e gestione. Le voci che non trovano posto vanno elencate al cliente, non scartate in silenzio.
- Poi si affronta **pagina per pagina** con il cliente. L'inventario e lo stato di ogni voce (riportato / da decidere / bloccato) sta in `docs/inventario-contenuti-sito-vecchio.md`.
- Non aggirare mai i blocchi anti-bot del sito vecchio: niente intestazioni false, niente scansioni aggressive. Le pagine bloccate si chiedono al cliente.

## Contenuti e dati
- Non inventare testi, numeri o certificazioni. Testi legali (privacy, cookie): collegare i documenti ufficiali del cliente, non scriverli.
- Foto: solo quelle del cliente.
- I dati personali (per esempio i curricula) non vanno copiati in questo repository pubblico.
- Velocità e accessibilità: `main.css` sotto i 30 KB, CLS 0, nessuna richiesta esterna, nessun font web, `prefers-reduced-motion` rispettato.

## Scelte tecniche concordate
- Astro statico con contenuti in Markdown, aggiornati dal cliente chiedendo a Claude.
- Funzioni: iscrizione online, archivio news completo, documenti e CCNL filtrabili. Area Lavoro: tenuta, curricula compresi, ma solo con consenso scritto non scaduto e file fuori dal repository pubblico (vedi `src/data/README.md`).
- HERO della home: prossimo evento o assemblea, pulsante Iscriviti, ultime comunicazioni, logo e presentazione.
