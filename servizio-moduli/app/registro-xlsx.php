<?php
declare(strict_types=1);

// Costruzione del registro delle iscrizioni (richieste online + archivio storico).
// Usata dall'esportazione protetta (registro.php) e, a ogni nuova iscrizione,
// per tenere aggiornato il file nella cartella privata.
require_once __DIR__ . '/archivio-storico.php';

function costruisci_registro(PDO $db, string $storage, bool $conStorico = true): \PhpOffice\PhpSpreadsheet\Spreadsheet {
    $rows=$db->query('SELECT * FROM iscrizioni ORDER BY ricevuta_il DESC')->fetchAll(PDO::FETCH_ASSOC);
    $cols=['Anno richiesta','Data richiesta','Nome','Cognome','Codice fiscale','Causale iscrizione','Iscritto anno precedente','Email','PEC','Telefono','Indirizzo','Località','Provincia o regione','CAP','Paese','Luogo di nascita','Data di nascita','Ordine di appartenenza','Luogo di lavoro','Metodo di pagamento','Riferimento ricevuta','Note aggiuntive','Privacy letta','Stato notifica','Riferimento'];
    $keys=['anno','ricevuta_il','nome','cognome','codice_fiscale','causale','iscritto_precedente','email','pec','telefono','indirizzo','localita','provincia','cap','paese','luogo_nascita','data_nascita','ordine','luogo_lavoro','pagamento','allegati','note','privacy','notifica','id'];
    $book=new PhpOffice\PhpSpreadsheet\Spreadsheet();$sheet=$book->getActiveSheet();$sheet->setTitle('Iscrizioni');
    foreach($cols as $i=>$v) $sheet->setCellValueExplicit([$i+1,1],$v,PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    foreach($rows as $n=>$row){$data=json_decode($row['dati'],true,512,JSON_THROW_ON_ERROR);$data['anno']=(string)$row['anno'];$data['ricevuta_il']=$row['ricevuta_il'];$data['allegati']=implode(', ',json_decode($row['allegati'],true,512,JSON_THROW_ON_ERROR));$data['privacy']='Sì';$data['notifica']=$row['notifica'];$data['id']=$row['id'];foreach($keys as $i=>$key)$sheet->setCellValueExplicit([$i+1,$n+2],(string)($data[$key]??''),PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        // Blu = già iscritto l'anno precedente (rinnovo); verde = non iscritto l'anno precedente (nuova iscrizione).
        $colore=(($data['iscritto_precedente']??'')==='si')?'FFBDD7EE':((($data['iscritto_precedente']??'')==='no')?'FFC6E0B4':null);
        if($colore!==null)$sheet->getStyle([1,$n+2,count($keys),$n+2])->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($colore);}
    $sheet->freezePane('A2');$sheet->setAutoFilter($sheet->calculateWorksheetDimension());
    $lc=count($keys)+2;
    $sheet->setCellValue([$lc,1],'BLU = già iscritto l\'anno precedente');$sheet->getStyle([$lc,1])->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFBDD7EE');
    $sheet->setCellValue([$lc,2],'VERDE = nuova iscrizione (non iscritto l\'anno precedente)');$sheet->getStyle([$lc,2])->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6E0B4');
    $sheet->getColumnDimensionByColumn($lc)->setWidth(52);
    if ($conStorico) aggiungi_archivio_storico($book, $storage);
    return $book;
}

// Riscrive il file aggiornato nella cartella privata. Se l'archivio storico manca
// o è incompleto non sovrascrive nulla: il file precedente resta valido.
function salva_registro_aggiornato(PDO $db, string $storage): string {
    $dest = $storage . '/iscrizioni-afant-aggiornato.xlsx';
    $lock = fopen($storage . '/registro.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) throw new RuntimeException('Blocco del registro non disponibile.');
    try {
        $book = costruisci_registro($db, $storage, true);
        $tmp = tempnam($storage, 'reg');
        if ($tmp === false) throw new RuntimeException('File temporaneo non disponibile.');
        (new PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($tmp);
        $book->disconnectWorksheets();
        chmod($tmp, 0600);
        if (is_file($dest)) copy($dest, $storage . '/iscrizioni-afant-precedente.xlsx');
        if (!rename($tmp, $dest)) { @unlink($tmp); throw new RuntimeException('Salvataggio del registro non riuscito.'); }
        return $dest;
    } finally { flock($lock, LOCK_UN); fclose($lock); }
}
