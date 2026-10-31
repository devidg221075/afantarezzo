# Dati del sito

- `eventi.json`: prossimi eventi mostrati nell'HERO della home. Formato di ogni voce:
  `{ "titolo": "...", "data": "2026-11-20", "ora": "21:15", "luogo": "...", "descrizione": "...", "link": "/notizie/..." }`.
  Se non ci sono eventi futuri, l'HERO mostra l'ultima comunicazione.
- `documenti.json`: schede dei documenti. Il campo `file` (percorso in `public/documenti/`) è vuoto finché i PDF non vengono caricati.
- `redirects.json`: indirizzi vecchi degli articoli verso i nuovi (generato dalla migrazione).

## Curricula (dati personali: NON vanno nel repository)

`src/data/curricula.json` e `public/curricula/` sono esclusi da Git. Formato di ogni voce:
`{ "nome": "...", "profilo": "...", "file": "/curricula/nome.pdf", "data": "2026-01-15", "consensoScritto": true, "scadenza": "2027-01-15" }`.
La pagina mostra solo le voci con `consensoScritto: true` e `scadenza` non superata. Senza il file, la sezione resta vuota.
