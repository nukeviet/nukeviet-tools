<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (isset($_GET['response_headers_detect'])) {
    exit(0);
}

define('NV_SYSTEM', true);

// Xac dinh thu muc goc cua site
define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));

require NV_ROOTDIR . '/includes/mainfile.php';
require NV_ROOTDIR . '/includes/core/user_functions.php';

require NV_ROOTDIR . '/src/Large-CSVReader.php';
require NV_ROOTDIR . '/src/geoidInfo.php';

if ($sys_info['ini_set_support']) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
}

/**
 * Bắt đầu tool
 */

$inputFileType = 'Csv';
$inputFileName = NV_ROOTDIR . '/libs/ip/GeoLite2-Country-Blocks-IPv4.csv';

// Số row mỗi chunk
$chunkSize = 10000;

$chunkFilter = new NukeViet\Files\ChunkReadFilter();

$reader = IOFactory::createReader($inputFileType);
$reader->setReadFilter($chunkFilter)->setContiguous(true);
$spreadsheet = new Spreadsheet();

$offsetRow = $nv_Request->get_int('offsetRow', 'get', 0);

$sheet = 0;
$startRow = 2 + $offsetRow;

$chunkFilter->setRows($startRow, $chunkSize);
$reader->setSheetIndex($sheet);
$reader->loadIntoExisting($inputFileName, $spreadsheet);
$sheetData = $spreadsheet->getActiveSheet();

$maxRow = $sheetData->getHighestRow();

echo('<pre><code>');

if ($maxRow <= 1) {
    echo('Kết thúc');
} else {
    for ($i = $startRow; $i <= $maxRow; $i++) {
        $CellValueA = $sheetData->getCell('A' . $i)->getValue();
        $CellValueB = $sheetData->getCell('B' . $i)->getValue();
        $CellValueC = $sheetData->getCell('C' . $i)->getValue();
        $CellValueD = $sheetData->getCell('D' . $i)->getValue();
        $CellValueE = $sheetData->getCell('E' . $i)->getValue();
        $CellValueF = $sheetData->getCell('F' . $i)->getValue();

        print_r($CellValueA . '|' . $CellValueB . '|' . $CellValueC . '|' . $CellValueD . '|' . $CellValueE . '|' . $CellValueF . "\n");
    }
    echo('<meta http-equiv="refresh" content="0;url=/' . basename(__FILE__) . '?offsetRow=' . ($offsetRow + $chunkSize) . '&t=' . time() . '">');
}

echo('</code></pre>');

