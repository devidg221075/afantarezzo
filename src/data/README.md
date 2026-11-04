# Dati del sito

- `eventi.json`: prossimi eventi mostrati nell'HERO della home. Formato di ogni voce:
  `{ "titolo": "...", "data": "2026-11-20", "ora": "21:15", "luogo": "...", "descrizione": "...", "link": "/notizie/..." }`.
  Se non ci sono eventi futuri, l'HERO mostra l'ultima comunicazione.
- `documenti.json`: schede dei documenti. Il campo `file` (percorso in `public/documenti/`) è vuoto finché i PDF non vengono caricati.
- `redirects.json`: indirizzi vecchi degli articoli verso i nuovi (generato dalla migrazione).

## Foto della hero

`hero.json` gestisce le fotografie di sfondo. La lista contiene le quattro fotografie fornite dal cliente. Se viene svuotata, resta lo sfondo grafico e non compaiono controlli. Titolo, pulsanti e riquadro della newsletter non cambiano struttura.

Salvare le foto del cliente in `public/hero/` e aggiungerle a `slides`:

```json
{
  "intervallo": 8000,
  "slides": [
    {
      "src": "/hero/nome-foto.webp",
      "larghezza": 1920,
      "altezza": 1080,
      "posizione": "50% 50%",
      "posizioneMobile": "65% 50%"
    }
  ]
}
```

Il nome e le dimensioni sopra sono un esempio: usare quelli reali del file. Le posizioni permettono di tenere visibile il soggetto nei ritagli desktop e mobile. Le fotografie sono decorative, sotto una velatura blu per la leggibilità del testo. Con una foto lo sfondo è fisso; con più foto si attivano dissolvenza, frecce e pausa. L'intervallo minimo è 5 secondi. La rotazione si ferma dopo un cambio manuale, mentre si usano i controlli o la scheda non è visibile; non parte se il sistema richiede movimento ridotto. Senza JavaScript resta visibile la prima foto.

## Curricula (dati personali: NON vanno nel repository)

`src/data/curricula.json` e `public/curricula/` sono esclusi da Git. Formato di ogni voce:
`{ "nome": "...", "profilo": "...", "file": "/curricula/nome.pdf", "data": "2026-01-15", "consensoScritto": true, "scadenza": "2027-01-15" }`.
La pagina mostra solo le voci con `consensoScritto: true` e `scadenza` non superata. Senza il file, la sezione resta vuota.
