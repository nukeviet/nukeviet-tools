<?php

/**
 * NUKEVIET Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

define('NV_ADMIN', true);
define('NV_MAINFILE', true);

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$base_siteurl = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
if ($base_siteurl == DIRECTORY_SEPARATOR) {
    $base_siteurl = '';
}
if (!empty($base_siteurl)) {
    $base_siteurl = str_replace(DIRECTORY_SEPARATOR, '/', $base_siteurl);
}
if (!empty($base_siteurl)) {
    $base_siteurl = preg_replace('/[\/]+$/', '', $base_siteurl);
}
if (!empty($base_siteurl)) {
    $base_siteurl = preg_replace('/^[\/]*(.*)$/', '/\\1', $base_siteurl);
}
$base_siteurl = $base_siteurl . '/';
$selfUrl = $base_siteurl . basename(__FILE__);

define('NV_ROOTDIR', str_replace('\\', '/', realpath(dirname(__FILE__) . '/')));

// Load dữ liệu nguồn
$global_config = [];
require NV_ROOTDIR . '/data/config/config_global.php';

if (isset($_POST['deleteSession'])) {
    unset($_SESSION['finalLangTranslator'], $_SESSION['finalLangModule']);
    header('Location:' . $selfUrl);
    exit();
}

$currentModule = 'laws';
$lang = 'vi';

// Biến chứa ngôn ngữ cuối cùng
$finalLangTranslator = $finalLangModule = [];

if (isset($_SESSION['finalLangTranslator']) and isset($_SESSION['finalLangModule'])) {
    // Nếu đã ghi session rồi thì đọc từ session
    $finalLangTranslator = $_SESSION['finalLangTranslator'];
    $finalLangModule = $_SESSION['finalLangModule'];
    echo '
        <form method="post" action="' . $selfUrl . '">
            <div>
                <p>Tiến trình này đang chạy dở, bạn có muốn xóa làm lại không</p>
                <input type="submit" name="deleteSession" value="Xóa làm lại">
            </div>
        </form>
    ';
} else {
    // Nếu có ngôn ngữ site thì đọc
    if (file_exists(NV_ROOTDIR . '/modules/' . $currentModule . '/language/' . $lang . '.php')) {
        $lang_translator = $lang_module = [];
        include NV_ROOTDIR . '/modules/' . $currentModule . '/language/' . $lang . '.php';

        $finalLangTranslator = $lang_translator;
        $finalLangModule = $lang_module;
    }
}

// Nếu tồn tại file ngôn ngữ admin
$workLang = [];
if (file_exists(NV_ROOTDIR . '/modules/' . $currentModule . '/language/admin_' . $lang . '.php')) {
    $lang_translator = $lang_module = [];
    include NV_ROOTDIR . '/modules/' . $currentModule . '/language/admin_' . $lang . '.php';

    // Nếu không có ngôn ngữ site thì info lang là của admin
    if (empty($finalLangTranslator)) {
        $finalLangTranslator = $lang_translator;
    }
    $workLang = $lang_module;
}

// Nếu không có ngôn ngữ site lẫn admin thì module này không có phần lang
if (empty($finalLangModule) and empty($workLang)) {
    exit('Không có ngôn ngữ');
}

/*
 * Bắt đầu loop để duyệt lang mới
 * Chỉ loop từ phần bắt đầu offset
 */
$offsetKey = isset($_POST['offsetKey']) ? (int) ($_POST['offsetKey']) : (isset($_GET['offsetKey']) ? (int) ($_GET['offsetKey']) : 0);
$currentKey = 0;
foreach ($workLang as $key => $value) {
    // Tới phần tiếp theo mới xử lý
    if ($currentKey >= $offsetKey) {
        if (!isset($finalLangModule[$key])) {
            // Nếu mà cái key này chưa có thì chỉ cần thêm vào
            $finalLangModule[$key] = $value;
        } elseif (isset($finalLangModule[$key]) and $finalLangModule[$key] != $value) {
            if (isset($_POST['chooseAdmin'])) {
                $finalLangModule[$key] = $value;
                $_SESSION['finalLangTranslator'] = $finalLangTranslator;
                $_SESSION['finalLangModule'] = $finalLangModule;
                header('Location:' . $selfUrl . '?offsetKey=' . ($currentKey + 1));
                exit();
            }
            if (isset($_POST['chooseSite'])) {
                $_SESSION['finalLangTranslator'] = $finalLangTranslator;
                $_SESSION['finalLangModule'] = $finalLangModule;
                header('Location:' . $selfUrl . '?offsetKey=' . ($currentKey + 1));
                exit();
            }
            /*
             * Nếu đã có mà giá trị khác nhau thì mới cho chọn
             * Còn đã có mà giá trị như nhau thì chỉ cần bỏ qua
             */
            echo '
                    <form method="post" action="' . $selfUrl . '">
                        <input type="hidden" name="offsetKey" value="' . $currentKey . '">
                        <div>
                            Key: <strong>' . $key . '</strong><br />
                            Site: <strong>' . htmlspecialchars($finalLangModule[$key]) . '</strong><br />
                            Admin: <strong>' . htmlspecialchars($value) . '</strong><br />
                            Chọn cái nào?<br />
                            <input type="submit" name="chooseSite" value="Chọn Site">
                            <input type="submit" name="chooseAdmin" value="Chọn Admin">
                        </div>
                    </form>
                ';

            /*
             * Ghi lại vào SESSION
             */
            $_SESSION['finalLangTranslator'] = $finalLangTranslator;
            $_SESSION['finalLangModule'] = $finalLangModule;

            exit();
        }
    }
    ++$currentKey;
}

/*
 * Ghi ra file
 */
if (isset($_POST['writeData'])) {
    if (preg_match('/^(0?\d|[1-2]{1}\d|3[0-1]{1})[\-\/\.]{1}(0?\d|1[0-2]{1})[\-\/\.]{1}(19[\d]{2}|20[\d]{2})[\-\/\.\,\\s]{2}(0?\d|[1]{1}\d|2[0-4]{1})[\-\/\.\:]{1}([0-5]?[0-9])$/', $finalLangTranslator['createdate'], $m)) {
        $createdate = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
    } elseif (preg_match('/^(0?\d|[1-2]{1}\d|3[0-1]{1})[\-\/\.]{1}(0?\d|1[0-2]{1})[\-\/\.]{1}(19[\d]{2}|20[\d]{2})$/', $finalLangTranslator['createdate'], $m)) {
        $createdate = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    } else {
        $createdate = time();
    }

    $content_lang = "<?php\n\n";
    $content_lang .= "/**\n";
    $content_lang .= " * NukeViet Content Management System\n";
    $content_lang .= " * @version 4.x\n";
    $content_lang .= " * @author VINADES.,JSC <contact@vinades.vn>\n";
    $content_lang .= " * @copyright (C) 2009-" . date('Y') . " VINADES.,JSC. All rights reserved\n";
    $content_lang .= " * @license GNU/GPL version 2 or any later version\n";
    $content_lang .= " * @see https://github.com/nukeviet The NukeViet CMS GitHub project\n";
    $content_lang .= " */\n";
    $content_lang .= "\nif (!defined('NV_MAINFILE')) {";
    $content_lang .= "\n    die('Stop!!!');\n}\n\n";

    $finalLangTranslator['info'] = (isset($finalLangTranslator['info'])) ? $finalLangTranslator['info'] : '';

    $content_lang .= "\$lang_translator['author'] = '" . str_replace(['(', ')'], ['<', '>'], $finalLangTranslator['author']) . "';\n";
    $content_lang .= "\$lang_translator['createdate'] = '" . $finalLangTranslator['createdate'] . "';\n";
    $content_lang .= "\$lang_translator['copyright'] = '" . $finalLangTranslator['copyright'] . "';\n";
    $content_lang .= "\$lang_translator['info'] = '" . $finalLangTranslator['info'] . "';\n";
    $content_lang .= "\$lang_translator['langtype'] = '" . $finalLangTranslator['langtype'] . "';\n";
    $content_lang .= "\n";

    foreach ($finalLangModule as $lang_key => $lang_value) {
        $lang_value = nv_unhtmlspecialchars($lang_value);
        $lang_value = str_replace("\'", "'", $lang_value);
        $lang_value = str_replace("'", "\'", $lang_value);
        $lang_value = nv_nl2br($lang_value);
        $lang_value = str_replace('<br/>', '<br />', $lang_value);

        $content_lang .= "\$lang_module['" . $lang_key . "'] = '" . $lang_value . "';\n";
    }

    // Xóa ngôn ngữ admin nếu có
    if (file_exists(NV_ROOTDIR . '/modules/' . $currentModule . '/language/admin_' . $lang . '.php')) {
        unlink(NV_ROOTDIR . '/modules/' . $currentModule . '/language/admin_' . $lang . '.php');
    }

    file_put_contents(NV_ROOTDIR . '/modules/' . $currentModule . '/language/' . $lang . '.php', $content_lang, LOCK_EX);

    // Xóa thông tin và làm lại
    unset($_SESSION['finalLangTranslator'], $_SESSION['finalLangModule']);
    header('Location:' . $selfUrl);
    exit();
}

echo '
    <form method="post" action="' . $selfUrl . '">
        <input type="hidden" name="offsetKey" value="' . $offsetKey . '">
        <div>
            <p>Đã thực hiện xong, bạn làm gì tiếp theo?</p>
            <input type="submit" name="writeData" value="Ghi dữ liệu và kết thúc">
            <input type="submit" name="deleteSession" value="Thực hiện lại từ đầu">
        </div>
    </form>
';

echo '<pre><code>';
echo htmlspecialchars(print_r($finalLangModule, true));
echo '</code></pre>';

/**
 * @param string $text
 * @param string $replacement
 * @return string
 */
function nv_nl2br($text, $replacement = '<br />')
{
    if (empty($text)) {
        return '';
    }

    return strtr($text, [
        "\r\n" => $replacement,
        "\r" => $replacement,
        "\n" => $replacement
    ]);
}

/**
 * @param string $string
 * @return string
 */
function nv_unhtmlspecialchars($string)
{
    if (empty($string)) {
        return $string;
    }

    if (is_array($string)) {
        $array_keys = array_keys($string);

        foreach ($array_keys as $key) {
            $string[$key] = nv_unhtmlspecialchars($string[$key]);
        }
    } else {
        $search = ['&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&#x005C;', '&#x002F;', '&#40;', '&#41;', '&#42;', '&#91;', '&#93;', '&#33;', '&#x3D;', '&#x23;', '&#x25;', '&#x5E;', '&#x3A;', '&#x7B;', '&#x7D;', '&#x60;', '&#x7E;'];
        $replace = ['&', '\'', '"', '<', '>', '\\', '/', '(', ')', '*', '[', ']', '!', '=', '#', '%', '^', ':', '{', '}', '`', '~'];

        $string = str_replace($search, $replace, $string);
    }

    return $string;
}
