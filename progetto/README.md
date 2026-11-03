# Sito A.FA.N.T. Arezzo

Sito statico con [Astro](https://astro.build), contenuti in Markdown. Le regole di lavoro del cliente sono in `CLAUDE.md`.

## Comandi

```bash
npm install
npm run dev               # sviluppo locale
npm run build             # sito finale in dist/
npm run build:anteprima   # copia per il cliente, con le note arancioni su cosa è da decidere
npm run check:css         # verifica: CSS sotto 30 KB, nessun font web, nessuna richiesta esterna
```

## Dove si aggiornano i contenuti

- **Notizie**: un file per articolo in `src/content/news/` (titolo, data, categoria nel blocco iniziale). Per pubblicare una notizia basta chiedere a Claude.
- **Prossimo evento nell'HERO**: `src/data/eventi.json` (vedi `src/data/README.md`).
- **Documenti**: `src/data/documenti.json` e i PDF in `public/documenti/`.
- **Pagine**: `src/pages/` e `src/content/pagine/`.

## Stato

Prima versione per l'anteprima. Articoli migrati dal sito vecchio (135), indirizzi vecchi con redirect verso `/notizie/...`. Mancano i PDF, e restano da decidere con il cliente i punti in `docs/esito-lettura-contenuti.md`.
