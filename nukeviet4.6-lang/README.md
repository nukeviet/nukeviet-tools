# Các công cụ

1. ConvertAll.php
2. ConvertLangBlock.php
3. CheckOldLang.php
4. CheckNewLangError.php
5. CheckOther.php
6. LangCompare.php

Đặt các tool này ngang thư mục gốc và chạy `php filename.php`. Riêng tool số 6 mở lên sửa tên module, ngôn ngữ và chạy qua https://domain.com/LangCompare.php

Mục số 4 để tìm các chỗ lỗi có thể tìm với regex sau `\$nv_Lang->get(Module|Global)\((\"|\')[a-zA-Z0-9\-\_\.]+(\"|\')\)[\s]*=`

### siteinfo.php

File này có dòng dạng `$lang_siteinfo = nv_get_lang_module($mod);`. Cần bỏ nó thay cách dùng `$lang_siteinfo` bằng `$nv_Lang->getModule('siteinfo_publtime')` bình thường. Không cần gọi global biến $nv_Lang

### Xử lý lang trong block global

Ta có module của block `$module = $block_config['module'];`  
Từ đó xác định được module_file của block `$modfile = $site_mods[$module]['module_file'];`   
Nếu block đặt trên module đó tức `$module_file == $modfile` thì lang cần xuất ra là 

```php
\NukeViet\Core\Language::$lang_module
```

Nếu block đặt trên modue khác thì

```php
$nv_Lang->loadModule($modfile, false, true);
```

Và lang xuất ra là

```php
\NukeViet\Core\Language::$tmplang_module
```

Xuất lang xong thì

```php
$nv_Lang->changeLang();
```

Để bỏ lang tạm đi
