# Verifica privacy e cookie (15 novembre 2026)

Controllo tecnico del sito rispetto al Regolamento (UE) 2016/679 (GDPR), al Codice privacy (D.Lgs. 196/2003, art. 122) e alle Linee guida cookie del Garante (provvedimento n. 231 del 10 giugno 2021). **Non è una certificazione né un parere legale**: nessun professionista ha ancora riletto i testi (decisione del cliente del 5 novembre).

## Cosa è a posto
- **Cookie:** il sito non imposta cookie (verificato nel browser: 0 cookie) e non usa analisi, profilazione, pubblicità, font o script esterni. Per i soli strumenti tecnici le Linee guida richiedono l'informativa, non il consenso. L'avviso in basso è quindi un'informativa breve, non un banner di consenso, e non blocca la navigazione.
- **Informativa art. 13:** titolare, finalità, basi giuridiche, destinatari, trasferimenti, conservazione, diritti (artt. 15-21), reclamo al Garante (art. 77), natura obbligatoria dei dati, assenza di decisioni automatizzate.
- **Informativa breve** vicino ai moduli (iscrizione e contatti), con rimando a quella completa.
- **Dati particolari (art. 9):** l'adesione all'associazione può rivelare un'attività professionale e di tutela; indicata la condizione dell'art. 9, par. 2, lett. d.
- **Minimizzazione:** PEC resa facoltativa (prima era obbligatoria); avvisi di non inserire dati sulla salute nelle note e nelle richieste.
- **Sicurezza:** HTTPS, archivio non pubblico, registro con credenziali, antispam e limite di invii all'ora per indirizzo.
- **Commercialista:** dichiarato in Chi siamo, nella pagina Iscrizione (pagamento) e nell'informativa (destinatari).

## Da decidere o confermare (il cliente)
1. **Cloudflare Turnstile.** Quando i moduli sono attivi il browser contatta Cloudflare (Stati Uniti). Cloudflare dichiara di basarsi sul Data Privacy Framework UE-USA (decisione della Commissione del 10 luglio 2023) e sulle clausole standard. Resta il contrasto con la regola di progetto "nessuna richiesta esterna". Alternativa senza terzi: antispam proprio (campo trappola, tempo minimo di compilazione, limite di invii già presente). Da decidere.
2. **Dati richiesti nell'iscrizione.** Restano obbligatori codice fiscale, luogo e data di nascita, indirizzo completo, telefono. Per il principio di minimizzazione (art. 5, par. 1, lett. c) conviene tenere obbligatori solo quelli necessari al registro soci e alla ricevuta. Da decidere quali.
3. **Commercialista.** Confermare se riceve i dati dei versamenti delle quote (così scritto nell'informativa) e a che titolo (responsabile del trattamento con nomina scritta, art. 28, oppure autonomo titolare).
4. **Serverplan.** Servono la nomina a responsabile del trattamento (art. 28) o i termini contrattuali equivalenti; confermare il Paese del datacenter.
5. **Cancellazione.** L'informativa dice che la fa il Consiglio Direttivo alla scadenza o su richiesta: è un impegno da mantenere (promemoria annuale).
6. **Registro dei trattamenti (art. 30).** Per un'associazione piccola può non essere obbligatorio, ma conviene tenerlo, soprattutto per i dati degli iscritti.
7. **Revisione.** Far leggere i testi, anche solo una volta, al commercialista o a un consulente privacy.
8. **Privacy dei curricula (Area Lavoro).** Restano vincolati al consenso scritto aggiornato di ciascuno (vedi `src/data/README.md`).

## Da verificare dopo la pubblicazione
- Intestazioni del server: nessun `set-cookie` oltre a quello di BitNinja che cancella un valore.
- Con i moduli attivi: cookie e richieste effettivamente creati da Turnstile, e aggiornamento dell'informativa se cambiano.
