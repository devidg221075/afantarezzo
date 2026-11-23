<?php
declare(strict_types=1);
// Prova del file Excel aggiornato a ogni iscrizione. Richiede "composer install" (vendor/).
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) { echo "Prova registro saltata: vendor/ non presente\n"; exit(0); }
require $autoload;
require dirname(__DIR__) . '/app/registro-xlsx.php';
function check(bool $x, string $m): void { if (!$x) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }
$dir = sys_get_temp_dir() . '/afant-prova-' . bin2hex(random_bytes(4)); mkdir($dir, 0700);
try {
    $db = new PDO('sqlite:' . $dir . '/registro.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('CREATE TABLE iscrizioni (id TEXT PRIMARY KEY, ricevuta_il TEXT NOT NULL, anno INTEGER NOT NULL, dati TEXT NOT NULL, allegati TEXT NOT NULL, notifica TEXT NOT NULL)');
    $ins = function (string $id, string $nome, string $when) use ($db) {
        $dati = ['tipo'=>'iscrizione','iscritto_precedente'=>($nome==='Bruno'?'no':'si'),'nome'=>$nome,'cognome'=>'Prova','email'=>'p@example.org','codice_fiscale'=>'RSSMRA80A01H501U','causale'=>'Iscrizione Afant 2027'];
        $db->prepare('INSERT INTO iscrizioni VALUES(?,?,?,?,?,?)')->execute([$id,$when,2027,json_encode($dati),'[]','inviata']);
    };
    // archivio storico finto: due fogli
    $h = new PhpOffice\PhpSpreadsheet\Spreadsheet(); $h->getActiveSheet()->setTitle('Storico A')->setCellValue('A1','vecchio socio');
    $h->createSheet()->setTitle('Storico B')->setCellValue('A1','altro');
    (new PhpOffice\PhpSpreadsheet\Writer\Xls($h))->save($dir . '/archivio-iscritti-storico.xls');
    // senza storico: non scrive nulla
    $ins('a1','Anna','2026-11-16T09:00:00+01:00');
    $ok = false; $tmpStor = $dir . '/archivio-iscritti-storico.xls'; rename($tmpStor, $tmpStor . '.x');
    try { salva_registro_aggiornato($db, $dir); } catch (Throwable $e) { $ok = true; }
    check($ok, 'senza archivio storico deve fallire');
    check(!is_file($dir . '/iscrizioni-afant-aggiornato.xlsx'), 'nessun file incompleto');
    rename($tmpStor . '.x', $tmpStor);
    // prima iscrizione
    $f = salva_registro_aggiornato($db, $dir);
    $book = PhpOffice\PhpSpreadsheet\IOFactory::load($f);
    check($book->getSheetCount() === 3, 'tre fogli (iscrizioni + 2 storici)');
    check($book->getSheet(0)->getHighestRow() === 2, 'una riga di iscrizione + intestazione');
    check($book->getSheet(0)->getCell('C2')->getValue() === 'Anna', 'nome in colonna C');
    // seconda iscrizione: il file si aggiorna, il precedente resta
    $ins('a2','Bruno','2026-11-17T10:00:00+01:00');
    salva_registro_aggiornato($db, $dir);
    $book = PhpOffice\PhpSpreadsheet\IOFactory::load($f);
    check($book->getSheet(0)->getHighestRow() === 3, 'due righe di iscrizione');
    check($book->getSheet(0)->getCell('C2')->getValue() === 'Bruno', 'la più recente in alto');
    $sh = $book->getSheet(0);
    check($sh->getStyle('C2')->getFill()->getStartColor()->getARGB() === 'FFC6E0B4', 'nuova iscrizione (no anno prima) in verde');
    check($sh->getStyle('C3')->getFill()->getStartColor()->getARGB() === 'FFBDD7EE', 'già iscritto anno prima in blu');
    check(is_file($dir . '/iscrizioni-afant-precedente.xlsx'), 'copia della versione precedente');
    $prev = PhpOffice\PhpSpreadsheet\IOFactory::load($dir . '/iscrizioni-afant-precedente.xlsx');
    check($prev->getSheet(0)->getHighestRow() === 2, 'la copia precedente ha una sola iscrizione');
    check((fileperms($f) & 0777) === 0600, 'permessi 0600');
    echo "Controlli registro superati\n";
} finally {
    foreach (glob($dir . '/*') ?: [] as $x) @unlink($x);
    @rmdir($dir);
}
