<?php

set_time_limit(0);

function list_all_file($dir = '', $base_dir = '')
{
    $file_list = array();

    if (is_dir($dir)) {
        $array_filedir = scandir($dir);

        foreach ($array_filedir as $v) {
            if ($v == '.' or $v == '..') {
                continue;
            }

            if (is_dir($dir . '/' . $v)) {
                foreach (list_all_file($dir . '/' . $v, $base_dir . '/' . $v) as $file) {
                    $file_list[] = $file;
                }
            } else {
                // if( $base_dir == '' and ( $v == 'index.html' or $v == 'index.htm' ) ) continue; // Khong di chuyen index.html
                if (
                    preg_match('/\.php$/', $v) and !preg_match('/^\/?(data|vendor)\//', $base_dir . '/' . $v) and
                    !preg_match('/\/?includes\/language/', $base_dir . '/' . $v) and
                    !preg_match('/\/?modules\/(.*?)\/language/', $base_dir . '/' . $v) and
                    !preg_match('/\/?themes\/(.*?)\/language/', $base_dir . '/' . $v)
                ) {
                    $file_list[] = preg_replace('/^\//', '', $base_dir . '/' . $v);
                }
            }
        }
    }

    return $file_list;
}

define('NV_ROOTDIR', 'D:/webroot/www/nukeviet4/tmp2.nukeviet4.my');

$allfiles = list_all_file(NV_ROOTDIR);

foreach ($allfiles as $filepath) {
    $filecontents = $filecontentsNew = file_get_contents(NV_ROOTDIR . '/' . $filepath);

    $filecontentsNew = preg_replace("/([a-zA-Z0-9\_]+)[\s]*\([\s]*\\\$module[\s]*\,[\s]*\\\$data\_block[\s]*\,[\s]*\\\$lang\_block[\s]*\)/", "\\1(\$module, \$data_block, \$nv_Lang)", $filecontentsNew);
    $filecontentsNew = preg_replace("/([a-zA-Z0-9\_]+)[\s]*\([\s]*\\\$module[\s]*\,[\s]*\\\$lang\_block[\s]*\)/", "\\1(\$module, \$nv_Lang)", $filecontentsNew);
    $filecontentsNew = preg_replace("/\*([\s\n\t\r]+)\* \@param mixed \\\$module([\s\n\t\r]+)\* \@param mixed \\\$data\_block([\s\n\t\r]+)\* \@param mixed \\\$lang\_block/", "*\\1* @param mixed \$module\\2* @param mixed \$data_block\\3* @param mixed \$nv_Lang", $filecontentsNew);
    $filecontentsNew = preg_replace("/\*([\s\n\t\r]+)\* \@param mixed \\\$module([\s\n\t\r]+)\* \@param mixed \\\$lang\_block/", "*\\1* @param mixed \$module\\2* @param mixed \$nv_Lang", $filecontentsNew);

    if ($filecontentsNew != $filecontents) {
        echo("Change: " . $filepath . "\n");
        file_put_contents(NV_ROOTDIR . '/' . $filepath, $filecontentsNew, LOCK_EX);
    }
}

echo("OK\n");
