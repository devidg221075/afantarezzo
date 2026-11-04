<?php
declare(strict_types=1);

// L'archivio storico resta fuori dalla cartella pubblica. Non modifica i dati
// delle richieste online: li aggiunge come fogli separati all'esportazione.
function aggiungi_archivio_storico(\PhpOffice\PhpSpreadsheet\Spreadsheet $export, string $storage): void {
    $path = $storage . '/archivio-iscritti-storico.xls';
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Archivio storico non disponibile.');
    }
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    $history = $reader->load($path);
    if ($history->getSheetCount() < 2) {
        throw new RuntimeException('Archivio storico incompleto.');
    }
    $sheets = [$history->getSheet(0), $history->getSheet(1)];
    foreach ($sheets as $sheet) {
        $export->addExternalSheet($sheet);
    }
}
