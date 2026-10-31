# Dati del sito

- `eventi.json`: prossimi eventi mostrati nell'HERO della home. Formato di ogni voce:
  `{ "titolo": "...", "data": "2026-11-20", "ora": "21:15", "luogo": "...", "descrizione": "...", "link": "/notizie/..." }`.
  Se non ci sono eventi futuri, l'HERO mostra l'ultima comunicazione.
- `documenti.json`: schede dei documenti. Il campo `file` (percorso in `public/documenti/`) è vuoto finché i PDF non vengono caricati.
- `redirects.json`: indirizzi vecchi degli articoli verso i nuovi (generato dalla migrazione).
