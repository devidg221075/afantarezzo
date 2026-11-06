# SEO: prova e pubblicazione definitiva

La build `npm run build:prova` è destinata solo a nuovo.afantarezzo.it: ogni pagina HTML del sito chiede `noindex, follow`. Il file robots.txt consente la scansione, necessaria perché i motori possano leggere la direttiva. I file e PDF pubblici non sono protetti da questa direttiva: noindex non è un controllo di accesso. Non usare questa build sul dominio definitivo.

La build `npm run build:produzione` produce sitemap.xml e robots.txt per https://www.afantarezzo.it e canonical assoluti sulle pagine. Eseguire le build con le variabili PUBLIC_FORMS_ENABLED e PUBLIC_TURNSTILE_SITEKEY previste per l'ambiente. La sitemap include le pagine canoniche effettivamente generate, non le vecchie pagine di redirect, i PDF o la pagina 404.

Il frammento `redirect-301.conf` contiene le regole Apache per le 135 vecchie notizie e sei vecchie pagine. Va integrato nel file .htaccess della radice definitiva, dopo aver conservato e controllato quello esistente. Non sostituire ciecamente il file esistente, che potrebbe contenere configurazioni essenziali. Verificare che mod_rewrite sia attivo e che il provider consenta le direttive. I redirect statici Astro restano una soluzione di ripiego e non inviano HTTP 301.

La vecchia pagina /codice-deontologico/ non è mappata: il relativo documento risulta mancante; individuare una destinazione equivalente prima di aggiungere un redirect. L'inventario è storico: verificare in Search Console e nel sito attuale altre URL e allegati, in particolare quelli ancora visitati, senza inventare corrispondenze o mandare tutte le URL alla home.

Al passaggio: controllare HTTPS su www e dominio senza www; predisporre un redirect permanente al dominio preferito senza alterare DNS/MX della posta; verificare pagine, 301, query string, moduli, registro e file; inviare la sitemap definitiva a Google Search Console. Controllare che nessuna direttiva noindex della prova arrivi in produzione. Una sitemap e i canonical indicano preferenze ai motori e non garantiscono indicizzazione o posizionamento.
