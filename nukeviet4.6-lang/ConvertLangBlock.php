<?php

/**
 * NUKEVIET Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

set_time_limit(0);

function list_all_file($dir = '', $base_dir = '')
{
    $file_list = [];

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

define('NV_ROOTDIR', str_replace('\\', '/', realpath(dirname(__FILE__) . '/')));

$allfiles = list_all_file(NV_ROOTDIR);

foreach ($allfiles as $filepath) {
    $filecontents = $filecontentsNew = file_get_contents(NV_ROOTDIR . '/' . $filepath);

    $filecontentsNew = preg_replace("/([a-zA-Z0-9\_]+)[\s]*\([\s]*\\\$module[\s]*\,[\s]*\\\$data\_block[\s]*\,[\s]*\\\$lang\_block[\s]*\)/", '\\1($module, $data_block)', $filecontentsNew);
    $filecontentsNew = preg_replace("/([a-zA-Z0-9\_]+)[\s]*\([\s]*\\\$module[\s]*\,[\s]*\\\$lang\_block[\s]*\)/", '\\1($module)', $filecontentsNew);
    $filecontentsNew = preg_replace("/\*([\s\n\t\r]+)\* \@param mixed \\\$module([\s\n\t\r]+)\* \@param mixed \\\$data\_block([\s\n\t\r]+)\* \@param mixed \\\$lang\_block/", '*\\1* @param mixed $module\\2* @param mixed $data_block', $filecontentsNew);
    $filecontentsNew = preg_replace("/\*([\s\n\t\r]+)\* \@param mixed \\\$module([\s\n\t\r]+)\* \@param mixed \\\$lang\_block/", '*\\1* @param mixed $module', $filecontentsNew);

    // Xử lý bổ sung biến $nv_Lang vào phần global của 2 hàm cấu hình block nếu chưa có

    // Hàm config
    $filecontentsNew = preg_replace_callback('/[\s]*function[\s]+[a-zA-Z0-9\_]+[\s]*\([\s]*\$module[\s]*\,[\s]*\$data\_block[\s]*\)[\r\n\s\t]*\{(.*)return[\s]*\$[a-z]+[\s]*\;[\r\n\s\t]*\}/isuU', function($matches) {
        $function = $matches[0];
        if (!preg_match('/\$nv_Lang\-\>/', $function)) {
            // Trong hàm config mà không có $nv_Lang thì không cần làm gì
            return $function;
        }

        if (preg_match("/global[\s]+\\$([a-zA-Z0-9\_\,\s\\$]+)\;/iU", $function, $m)) {
            // Nếu có dòng global thì chỉ cần bổ sung $nv_Lang vào đó
            $m[1] = '$' . $m[1];
            $array_variable = array_map('trim', explode(',', $m[1]));

            $newVariable = [];
            $isGlobaled = false;

            foreach ($array_variable as $vv) {
                if (!($vv == '$lang_global' or $vv == '$lang_module' or $vv == '$lang_block')) {
                    $newVariable[] = $vv;
                }
                if ($vv == '$nv_Lang') {
                    $isGlobaled = true;
                }
            }
            if (!$isGlobaled) {
                $newVariable[] = '$nv_Lang';
            }
            $array_variable = 'global ' . implode(', ', $newVariable) . ';';

            if ($array_variable != $m[0]) {
                $function = str_replace($m[0], $array_variable, $function);
            }
        } else {
            // Không có dòng global thì sinh ra
            $space = '';
            if (preg_match('/^([\s]+)function/i', $function, $m)) {
                $space = str_replace(["\r", "\n"], '', $m[1]);
                $space = str_replace("\t", '    ', $space);
            }
            $space .= '    ';
            $function = preg_replace('/function([^\{]+)\{/isuU', "function\\1{\n" . $space . "global \$nv_Lang;\n", $function);
        }
        return $function;
    }, $filecontentsNew);

    // Hàm config submit
    $filecontentsNew = preg_replace_callback('/[\s]*function[\s]+[a-zA-Z0-9\_]+[\s]*\([\s]*\$module[\s]*\)[\r\n\s\t]*\{(.*)return[\s]*\$[a-z]+[\s]*\;[\r\n\s\t]*\}/isuU', function($matches) {
        $function = $matches[0];
        if (!preg_match('/\$nv_Lang\-\>/', $function)) {
            // Trong hàm config mà không có $nv_Lang thì không cần làm gì
            return $function;
        }

        if (preg_match("/global[\s]+\\$([a-zA-Z0-9\_\,\s\\$]+)\;/iU", $function, $m)) {
            // Nếu có dòng global thì chỉ cần bổ sung $nv_Lang vào đó
            $m[1] = '$' . $m[1];
            $array_variable = array_map('trim', explode(',', $m[1]));

            $newVariable = [];
            $isGlobaled = false;

            foreach ($array_variable as $vv) {
                if (!($vv == '$lang_global' or $vv == '$lang_module' or $vv == '$lang_block')) {
                    $newVariable[] = $vv;
                }
                if ($vv == '$nv_Lang') {
                    $isGlobaled = true;
                }
            }
            if (!$isGlobaled) {
                $newVariable[] = '$nv_Lang';
            }
            $array_variable = 'global ' . implode(', ', $newVariable) . ';';

            if ($array_variable != $m[0]) {
                $function = str_replace($m[0], $array_variable, $function);
            }
        } else {
            // Không có dòng global thì sinh ra
            $space = '';
            if (preg_match('/^([\s]+)function/i', $function, $m)) {
                $space = str_replace(["\r", "\n"], '', $m[1]);
                $space = str_replace("\t", '    ', $space);
            }
            $space .= '    ';
            $function = preg_replace('/function([^\{]+)\{/isuU', "function\\1{\n" . $space . "global \$nv_Lang;\n", $function);
        }
        return $function;
    }, $filecontentsNew);

    if ($filecontentsNew != $filecontents) {
        echo 'Change: ' . $filepath . "\n";
        file_put_contents(NV_ROOTDIR . '/' . $filepath, $filecontentsNew, LOCK_EX);
    }
}

echo "OK\n";
