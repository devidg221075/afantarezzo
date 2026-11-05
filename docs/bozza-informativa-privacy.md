# Bozza informativa privacy: parti da completare

Stato: **bozza, non pubblicata**. La pagina `src/pages/privacy.astro` resta com'è finché il cliente non approva queste parti. Il testo va fatto rivedere a chi segue la privacy dell'associazione (consulente o DPO) prima della pubblicazione: non è consulenza legale.

Le parole tra [parentesi quadre] sono fatti che solo il cliente o il server conoscono. Non vanno inventate: vanno confermate prima di inserirle nella pagina.

## 1. Appartenenza all'associazione (sostituisce "AFANT sta verificando…")

Dallo statuto (pagina 1, già tra i documenti del sito): senza scopo di lucro, apolitica e apartitica (art. 2); tutela gli interessi morali, professionali ed economici degli iscritti e promuove contratti collettivi (art. 4); l'adesione prescinde dall'iscrizione a sindacati (art. 2).

> L'iscrizione ad A.FA.N.T. Arezzo, associazione senza scopo di lucro dei farmacisti non titolari della provincia di Arezzo, può rivelare che l'iscritto è un lavoratore dipendente e l'adesione a un'associazione che tutela gli interessi professionali degli iscritti. Tali informazioni sono trattate solo da AFANT, nell'ambito delle proprie finalità associative e a favore dei propri iscritti, con le garanzie previste dall'art. 9, par. 2, lett. d del GDPR, e non vengono comunicate all'esterno senza il consenso dell'interessato.

Resta da far confermare al consulente privacy se la lett. d è la condizione corretta per questa associazione.

## 2. Conservazione e cancellazione (sostituisce "da verificare e attuare")

> I dati delle richieste di contatto sono conservati per 12 mesi dalla chiusura della richiesta. I dati degli iscritti sono conservati per la durata del rapporto associativo e per [10 anni] dopo la sua cessazione. Le richieste di iscrizione rifiutate o non completate sono conservate per 12 mesi. Trascorsi questi periodi i dati sono cancellati [dall'archivio del sito, dalla casella di posta e dal registro; le copie di backup sono sovrascritte entro [N] giorni: DA CONFERMARE sul server].

Da fare tecnicamente: oggi la cancellazione non è automatica. O si imposta una procedura manuale con scadenza annuale, o si scrive uno script. Decide il cliente.

## 3. Fornitori e trasferimenti fuori SEE (sostituisce "da verificare")

> I dati sono ospitati su un server [VPS Serverplan, in [PAESE: DA CONFERMARE]]. La posta è gestita da [FORNITORE: DA CONFERMARE]. Per proteggere i moduli dalle richieste automatiche si usa Cloudflare Turnstile, che comunica con Cloudflare, società con sede fuori dallo Spazio economico europeo; il trasferimento è coperto da [decisione di adeguatezza / clausole contrattuali standard: DA CONFERMARE con le condizioni Cloudflare].

## 4. Cookie e memoria del browser (già inserito nella pagina Privacy, 5 novembre 2026)

> Il sito non imposta cookie e non usa strumenti di analisi o profilazione. Usa solo la memoria di sessione del browser per ricordare la chiusura dell'avviso privacy fino alla fine della sessione.

Verificato il 5 novembre 2026 sulle pagine pubblicate (Contatti, Iscrizione, Notizie, Privacy): l'unico `set-cookie` è `backend-port=` con `Max-Age=0` (cancella un valore, lo invia il sistema BitNinja). La home non è stata controllata perché il server ha chiuso la connessione.

## 5. Elenco delle conferme richieste al cliente

Risolto dallo statuto (art. 1): la sede è in via Newton 48, presso l'Ordine dei farmacisti. L'indirizzo nel sito è quindi corretto. Natura dell'associazione: vedi sezione 1.

1. Durata di conservazione dei dati degli iscritti (10 anni è già nella pagina: confermare).
2. Dove si trova il server e chi gestisce la posta.
3. Esistenza e durata dei backup.
4. Chi è la persona autorizzata a ricevere le richieste e le email dei moduli.
5. Chi deve rivedere il testo finale (consulente privacy).
6. P.IVA / codice fiscale: lo statuto non li riporta.
