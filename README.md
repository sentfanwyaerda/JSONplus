# JSONplus
**JSONplus** is a simple PHP-class extending the default JSON processing with *datalist*s and standard pretty printing.

```php
define("JSONplus_DATALIST_ROOT", dirname(__FILE__).'/'); #optional

$value = JSONplus::decode($json, TRUE);	#json_decode($json, TRUE);
$json  = JSONplus::encode($value);	#json_encode($value);
```

## Datalists example:

```json
{"id":"userlist","options":<datalist:users>}
```

**JSONplus** will include the datalist *users* (located at `JSONplus_DATALIST_ROOT/users.json`).

## CSV compatability
JSONplus can read CSV files, and turn them into JSON-table `[{}]`, and export these tables back to CSV. Use `JSONplus::import_csv`, `JSONplus::import_csv_file`, `JSONplus::export_csv`, `JSONplus::export_csv_file`, `JSONplus::is_table` and `JSONplus::get_columns`.

## JSONplus::worker

A simple worker script can handle several ways of processing a file.

```php
<?php
require_once(dirname(__FILE__).'/JSONplus.php');
$raw = JSONplus::worker('raw'); #string
/*or*/
$json = JSONplus::worker('json'); #array
?>
```

This code can process:
```
php -f worker.php a=1 #results into $_GET['a'] = 1
php -f worker.php set.json
cat set.json | php -f worker.php
http://.../worker.php?file=set.json
http://.../worker.php #process content of $_POST['json']
```
