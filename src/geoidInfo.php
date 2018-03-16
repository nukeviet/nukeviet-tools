<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

if (!defined('NV_MAINFILE'))
    die('Stop!!!');

$geoinfoFile = NV_ROOTDIR . '/libs/countryInfo.txt';

if (!file_exists($geoinfoFile)) {
    trigger_error('No file libs/countryInfo.txt', 256);
}

$array_geo_info = array();
$handle = fopen($geoinfoFile, 'r');

while (($buffer = fgets($handle, 4096)) !== false) {
    $buffer = trim($buffer);
    if (strpos($buffer, '#') === 0) {
        continue;
    }
    $buffer = explode("\t", $buffer);
    if (isset($buffer[16])) {
        $array_geo_info[$buffer[16]] = $buffer[0];
    } else {
        trigger_error('Error: countryInfo.txt get geoid false', 256);
    }
}
if (!feof($handle)) {
    trigger_error('Error: unexpected fgets() fail', 256);
}
fclose($handle);

unset($geoinfoFile, $handle, $buffer);
