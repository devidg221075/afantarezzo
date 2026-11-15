#!/usr/bin/env bash
# Installazione del servizio moduli AFANT su cPanel (da lanciare nel Terminale del cPanel).
# Si può rilanciare senza rischi: non sovrascrive config.php né i dati in dati/.
# Non chiede e non scrive password o chiavi: le inserisci tu in config.php.
set -u
QUI="$(cd "$(dirname "$0")" && pwd)"
BASE="${AFANT_BASE:-$HOME/afant-privato}"
DOCROOT="${AFANT_DOCROOT:-$HOME/public_html}"
SITO="${AFANT_SITO:-www.afantarezzo.it}"
PROBLEMI=0
ok() { echo "  [OK]       $*"; }
ko() { echo "  [DA FARE]  $*"; PROBLEMI=$((PROBLEMI+1)); }

echo "== 1. PHP (serve 8.3 o superiore)"
PHP=""
for c in "${AFANT_PHP:-}" php /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php; do
  [ -n "$c" ] || continue
  command -v "$c" >/dev/null 2>&1 || continue
  "$c" -r 'exit(PHP_VERSION_ID>=80300?0:1);' 2>/dev/null && { PHP="$(command -v "$c")"; break; }
done
if [ -z "$PHP" ]; then ko "PHP 8.3 non trovato: in cPanel, MultiPHP Manager, scegli PHP 8.3 per il dominio"; echo; echo "Interrotto."; exit 1; fi
ok "PHP: $PHP ($("$PHP" -r 'echo PHP_VERSION;'))"
for e in pdo_sqlite curl fileinfo mbstring openssl zip xml gd; do
  "$PHP" -m | grep -qix "$e" && ok "estensione $e" || ko "estensione $e mancante: in cPanel, Select PHP Version / Extensions, attivala"
done

echo "== 2. Cartella privata: $BASE"
mkdir -p "$BASE/app" "$BASE/dati" || { ko "impossibile creare $BASE"; exit 1; }
case "$(realpath "$BASE")/" in "$(realpath "$DOCROOT")"/*) ko "la cartella privata è DENTRO la cartella pubblica ($DOCROOT): spostala fuori";; *) ok "fuori dalla cartella pubblica";; esac
cp -r "$QUI/app/." "$BASE/app/" && cp "$QUI/composer.json" "$QUI/composer.lock" "$BASE/" && ok "file del servizio copiati"
chmod 700 "$BASE" "$BASE/dati"; chmod 750 "$BASE/app"; ok "permessi: cartella e dati/ solo tuoi (700)"

echo "== 3. Librerie (Composer)"
COMPOSER=""
if command -v composer >/dev/null 2>&1; then COMPOSER="$(command -v composer)"
elif [ -f "$BASE/composer.phar" ]; then COMPOSER="$BASE/composer.phar"
else
  echo "  Composer non presente: lo scarico dal sito ufficiale con verifica della firma"
  ( cd "$BASE" && EXPECTED="$(curl -fsS https://composer.github.io/installer.sig)" && "$PHP" -r "copy('https://getcomposer.org/installer','composer-setup.php');" \
    && ACTUAL="$("$PHP" -r "echo hash_file('sha384','composer-setup.php');")" && [ "$EXPECTED" = "$ACTUAL" ] \
    && "$PHP" composer-setup.php --quiet && rm -f composer-setup.php ) && COMPOSER="$BASE/composer.phar" || ko "Composer non scaricato (firma o rete): chiedi a Serverplan di installarlo"
fi
if [ -n "$COMPOSER" ]; then
  ( cd "$BASE" && "$PHP" "$COMPOSER" install --no-dev --no-interaction --optimize-autoloader >/dev/null 2>&1 ) && ok "librerie installate in $BASE/vendor" || ko "installazione librerie non riuscita (rilancia senza >/dev/null per vedere l'errore)"
fi
"$PHP" -r 'require $argv[1]."/vendor/autoload.php"; exit(class_exists("PhpOffice\\PhpSpreadsheet\\Spreadsheet") && class_exists("PHPMailer\\PHPMailer\\PHPMailer") ? 0 : 1);' "$BASE" 2>/dev/null && ok "PhpSpreadsheet e PHPMailer caricabili" || ko "librerie non caricabili"

echo "== 4. Configurazione ($BASE/config.php)"
if [ -f "$BASE/config.php" ]; then ok "config.php già presente: non lo tocco"
else
  sed "s/'site_host' => '[^']*'/'site_host' => '$SITO'/" "$BASE/app/config.example.php" > "$BASE/config.php" && chmod 600 "$BASE/config.php" && ok "config.php creato da esempio (enabled = false, site_host = $SITO)"
fi
chmod 600 "$BASE/config.php"
for k in smtp_password turnstile_secret admin_user admin_password_hash; do
  grep -Eq "'$k' => ''" "$BASE/config.php" && ko "config.php: compila '$k' (a mano, mai in chat)"
done
grep -Eq "'enabled' => false" "$BASE/config.php" && echo "  [INFO]     enabled = false: i moduli restano spenti finché non lo cambi tu"
"$PHP" -l "$BASE/config.php" >/dev/null && ok "config.php valido"

echo "== 5. Archivio storico"
[ -f "$BASE/dati/archivio-iscritti-storico.xls" ] && ok "archivio-iscritti-storico.xls presente" || ko "copia archivio-iscritti-storico.xls in $BASE/dati/ (File Manager)"

echo "== 6. Punti di accesso nel sito: $DOCROOT/api"
mkdir -p "$DOCROOT/api"
for f in invia.php registro.php; do
  sed "s#/home/afantarezzo/afant-privato#$BASE#g" "$QUI/public/api/$f" > "$DOCROOT/api/$f" && chmod 644 "$DOCROOT/api/$f"
  "$PHP" -l "$DOCROOT/api/$f" >/dev/null && ok "$f installato" || ko "$f non valido"
done

if [ -z "${AFANT_NOWEB:-}" ] && command -v curl >/dev/null 2>&1; then
  echo "== 7. Controlli dall'esterno ($SITO)"
  code() { curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$1"; }
  for u in "/afant-privato/config.php" "/afant-privato/dati/registro.sqlite" "/config.php"; do
    c="$(code "https://$SITO$u")"; [ "$c" = "200" ] && ko "ATTENZIONE: https://$SITO$u risponde 200 (file esposto!)" || ok "$u non raggiungibile ($c)"
  done
  c="$(code "https://$SITO/api/registro.php")"; case "$c" in 401|503) ok "registro.php protetto ($c)";; 200) ko "registro.php risponde 200 senza password!";; *) echo "  [INFO]     registro.php: $c";; esac
fi

echo
if [ "$PROBLEMI" -eq 0 ]; then echo "Tutto a posto. Resta da compilare config.php e fare le prove."; else echo "Punti da sistemare: $PROBLEMI (vedi [DA FARE] sopra). Poi rilancia: bash installa-su-cpanel.sh"; fi
