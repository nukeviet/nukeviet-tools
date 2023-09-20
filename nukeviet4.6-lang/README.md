# Các công cụ

1. ConvertAll.php
2. ConvertLangBlock.php
3. CheckOldLang.php
4. CheckNewLangError.php
5. CheckOther.php
6. LangCompare.php

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
