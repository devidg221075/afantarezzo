# Stato dei lavori (16 novembre 2026)

## Fatto e pubblicato su `main`
- Sito Astro completo, ricerca in testata (prova su 150 pagine: desktop 150/150, mobile 150/150, 0 problemi), categoria Appuntamenti, convocazione del 25 novembre 2026, modulo iscrizione 2027, privacy e cookie riscritte.
- Servizio PHP dei moduli (`servizio-moduli/`) installato su cPanel; il registro Excel si aggiorna da solo a ogni iscrizione e viene allegato alla mail di notifica.

## Aperto
- Contatti: primo invio rifiutato con "Verifica antispam non riuscita". Serve la riga `AFANT: turnstile ...` nel registro errori di cPanel (dopo aver caricato `common.php` e `validation.php` aggiornati in `afant-privato/app`).
  - `invalid-input-secret` / `invalid-input-response`: chiave segreta Turnstile errata in `config.php`.
  - `timeout-or-duplicate`: riprovare entro 1-2 minuti.
  - connessione a Cloudflare non riuscita: ticket a Serverplan.
  - dominio o azione diversi: errore del sito.
- Poi: prova reale contatti (mail al Consiglio + ricevuta al mittente) e iscrizione 2027; pulizia richieste di prova, `app-vecchio`, `vendor-vecchio`, vecchi ZIP; ZIP con moduli attivi (`PUBLIC_FORMS_ENABLED=1`, `PUBLIC_TURNSTILE_SITEKEY=0x4AAAAAAFxvf9xc30R8n2pH`).

## Decisioni del cliente ancora da prendere
- Turnstile contro la regola "nessuna richiesta esterna".
- Campi obbligatori dell'iscrizione; ruolo del commercialista e nomina art. 28; Serverplan art. 28 e paese del datacenter.
- Revisione professionale del testo privacy; modulo iscrizione 2027 (PDF); link social della convocazione.
- Segnalare a Serverplan: l'IP 77.39.210.106:2083 ha mostrato la pagina di un altro cliente dopo un blocco BitNinja.

Guida passo passo: `docs/attivazione-moduli.md`.
