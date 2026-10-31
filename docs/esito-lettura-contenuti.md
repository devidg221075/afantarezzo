# Esito della lettura dei contenuti del sito vecchio

Letti il 31/10/2026: 135 articoli, 20 pagine, 73 schede-documento (26 sono curricula: contati, non letti, dati personali). Restano 2 voci da recuperare (`/stage/`, bloccata dal firewall; `/newsletter-2-giugno-2023-2/`, connessione interrotta).

**Limite importante**: delle schede-documento sono stati letti solo i metadati (titolo, data, dimensione). Il cliente ha inviato prima un archivio con **5 PDF distinti** (28 file, ma 23 sono copie identiche), letti e collegati: piattaforma CCNL 2024-2027 (scheda documenti), convocazioni assemblea Conasfa 2024 e 2026, lettera dell'Ordine con i risultati elettorali 2024, locandina della serata del 16/2/2024. **Mancano i PDF delle altre 46 schede** (statuto, codice deontologico, CCNL, ECM, Enpaf, moduli, lettere Conasfa/Sinasfa…): vanno recuperati dal cliente.

## Che cosa contiene il sito

- **Archivio news (135 articoli, 2014-2026)**: convocazioni di assemblee (bilancio, elezioni, straordinarie), corsi e serate formative gratuite, newsletter, comunicati Conasfa/Sinasfa/Enpaf, circolari ECM, risultati elettorali.
- **Pagine**: la nostra storia, codice deontologico, statuto, iscrizione, CCNL, presentazione Sinasfa, link utili, lavoro, cerco lavoro, contatti (due versioni), privacy.
- **Documenti**: CCNL (8), altri contrattuali (4), ECM (7), Enpaf (8), Conasfa/Sinasfa (9), moduli (7), statuto e codice (2), convegni (2).

## Problemi di contenuto da risolvere

- **Articoli vuoti o con solo titolo**: assemblea marzo 2014, elezioni maggio 2014, assemblea ottobre 2014, serata stipsi (solo immagine), comunicati Conasfa/Enpaf 2018, convegno Farmacista+, guida ECM e altri (circa 20 sotto le 45 parole).
- **Allegati sbagliati**: lettera al Ministero della Salute e AIFA e "Dimissioni ospedaliere" puntano al file "misure anticovid"; lettera Conasfa a FOFI punta al questionario Q2; consulenza buste paga con allegato "Bonus 80 euro".
- **URL scambiati**: corso di cosmetica 2019 e assemblea bilancio 2019.
- **Circolare non scaricabile**: elezioni Ordine 2014, file marcato "File privato - accesso vietato".
- **Errori nel testo**: anno sbagliato nella convocazione del bilancio 2025 ("26 marzo 2025"), data di iscrizione 2016 in un articolo del gennaio 2015, scadenza già passata nella serata Enpaf 2016.
- **Segnaposto "LINK" senza indirizzo**: 15 articoli (deleghe, questionari, iscrizione).
- **Doppioni**: ogni serata formativa compare spesso due volte (assemblea e titolo della serata); newsletter che ripetono articoli già pubblicati; pagine Home, News e primo articolo identici.
- **Tag incoerenti** e circa 50 tag usati una volta sola.
- **Link esterni probabilmente rotti** (video, Cogeaps con sessione, Farmacista33, forms).

## Dati sensibili o da confermare (non copiati nel repository)

- **IBAN**: compare in 16 testi, incluso un IBAN intestato a una persona fisica in due circolari ECM del 2015-2016 (da non riportare). L'IBAN dell'associazione va confermato dal cliente.
- **Nomi**: cariche dell'associazione e dell'Ordine, relatori, nomi nei report; email personale della referente "cerco lavoro".
- **Telefono**: 370 3613108 (piè di pagina, comunicati, Contatti AFANT) contro +39 331 84 52 555 (pagina Contatti): quale è quello ufficiale?
- **Articoli di terzi** (Farmacista33, comunicati): da riportare con fonte e data, valutando il diritto d'autore.
- **Credito web agency** nel piè di pagina e copyright 2014: da decidere.

## Ancora utili oggi (da verificare sulle norme attuali)

Newsletter giugno 2026 e 2025 (assicurazione colpa grave, CCNL), novembre 2024 (fondi pensione e sanitari), previdenza complementare Sinasfa, dimissioni ospedaliere, guida Enpaf, esoneri ECM, controllo dell'indennità quadri in busta paga, modulo di delega, statuto, codice deontologico, CCNL vigenti.

## Residui del tema demo (da escludere, conferma del cliente)

About us, About us 2, Price Table, My Account, 29 voci "portfolio", barra laterale del tema, contatori download, segnaposto dei plugin.

## Decisioni da prendere con il cliente (una alla volta)

1. Recupero dei PDF e degli allegati: **42 schede su 55 con file**. Il cliente manda per primi codice deontologico e delega assemblea; gli altri 11 (storici) restano da recuperare.
2. Le 2 pagine ancora da leggere.
3. ~~Telefono ufficiale~~ **deciso: 370 3613108** (l'altro numero non è riportato); resta da decidere se serve un modulo contatti online.
4. ~~Area Lavoro~~ **deciso: tenerla, curricula compresi**. Pagina `/lavoro/` creata; i curricula non stanno nel repository pubblico (file privato escluso da Git). Da fare: consenso scritto aggiornato di ciascuno (26 curricula del 2015-2023), dove ospitare i file, scadenze.
5. ~~IBAN~~ **deciso: pubblicare anche negli articoli storici.** Rimessi i due conti dell'associazione (attuale, nella pagina Iscrizione e negli articoli 2024; precedente `…1108`, in 12 articoli 2015-2020, da verificare se ancora attivo). Il conto BancoPosta intestato a un privato (2 circolari ECM) resta non riportato.
6. Cariche e nomi: quali pubblicare (consiglio attuale, storico).
7. Documenti storici (Covid, ECM 2014-2016, Enpaf 2015-2018): archivio con filtri per anno e tema.
8. Doppioni e articoli vuoti: unire, completare o archiviare.
9. ~~Link utili~~ **indirizzi recuperati** dall'HTML del sito vecchio (14 voci; il duplicato "Regione Toscana" tolto); da verificare che funzionino ancora (quasi tutti `http`).
10. Privacy e cookie: collegare i documenti ufficiali del cliente.
11. HERO senza evento futuro: cosa mostrare.

## Stato della migrazione (aggiornato)

- **Articoli**: 135 migrati in `src/content/news/` come **riportati, da rivedere**. IBAN dell'associazione ripristinati (scelta del cliente); rimosso solo il conto personale di un privato, con il suo nome, nelle due circolari ECM. I testi vuoti o minimi, i "LINK" senza indirizzo e gli allegati da recuperare sono segnalati nel file (campo `segnalazioni`) e nell'anteprima.
- **Pagine**: riportate "La nostra storia" (senza composizione del consiglio, da confermare), Iscrizione (IBAN da confermare, modulo online da decidere), Contatti (telefono da decidere), CCNL e Documenti (schede senza file). **Non ancora riportate**: Codice deontologico e Statuto (solo con i PDF), Presentazione Sinasfa, Link utili (mancano gli indirizzi), Lavoro e Cerco lavoro, Privacy (si collegano i documenti ufficiali).
- **Documenti**: 47 schede con tema e stato proposti automaticamente; PDF da recuperare.
- **Ancora da leggere**: `/stage/` e `/newsletter-2-giugno-2023-2/`.

## Documenti: archivio completo ricevuto (aggiornato)

Dal secondo archivio (76 file) sono stati collegati **42 schede su 55** (27 PDF, 12 Word, 2 immagini, 1 ODT); i 29 curriculum **non sono stati aperti né copiati** (dati personali). Aggiunte 8 schede che nel sito vecchio non avevano scheda propria (moduli di iscrizione 2015, 2016, 2017, 2020, 2021; versione 8/9/2015 del volantino del convegno; avviso ESTAR 2015 e relativo schema di domanda).

- **Ancora senza file (13)**: codice deontologico, CCNL 2009, ECM 2014, delega assemblea, lettera Conasfa a Federfarma, lettera Conasfa a FOFI, lettera al Ministero della Salute e AIFA, newsletter Enpaf 2015, domanda borsa di studio 2015 Enpaf, comunicato Conasfa e comunicato Enpaf del 23/5/2018, circolare PEC gennaio 2015, circolare accordo Afant-Federfarma.
- **Statuto**: il PDF è una scansione da iPhone senza testo (3 pagine, circa 13 MB): non accessibile e pesante. Da sostituire con una versione leggibile.
- **Dati del sito vecchio inaffidabili**: dimensioni e date delle schede erano spesso sbagliate (es. molte "237,05 KB"; il CCNL 7/9/2021 risultava del 27/12/2024). Nel sito nuovo la dimensione è quella reale e la colonna "Pubblicato" riporta la data della scheda, non del documento.
- **Versioni precedenti non pubblicate** (3 moduli iscrizione 2020 con prefisso `wpdm_`, il Contratto integrativo regionale in Word, copie duplicate): da confermare con il cliente.
- **Avviso ESTAR e schema di domanda**: nessun articolo li cita; probabile allegato di un articolo non letto (`/stage/`?).

## Pagine riportate (aggiornamento)

Riportate: Home, La nostra storia, Iscrizione, Contatti, Notizie, Documenti, CCNL, Lavoro, **Link utili** (14 voci) e **Presentazione Sinasfa** (testo originale). **Ancora da fare**: Privacy (si collegano i documenti ufficiali del cliente, che non ho), pagine "Codice deontologico" e "Statuto" (sono schede di documenti: il codice aspetta il file), `/stage/` e `/newsletter-2-giugno-2023-2/` (da leggere).
