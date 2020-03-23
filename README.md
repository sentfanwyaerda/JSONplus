# JSONplus
**JSONplus** is a simple yet powerful PHP-class extending the default JSON processing with standard pretty printing and additional features.

```php
$value = JSONplus::decode($json, TRUE);	#json_decode($json, TRUE);
$json  = JSONplus::encode($value);	#json_encode($value);
```

## Feautures
- Pretty printing, which allows people to read the data and lets version management track minor changes efficiently
- Data-validation through [Schema](https://json-schema.org/)
- Datalists: the re-use of datastructures
- Negative numbers
- A worker-method for commandline requests
- Flattening to table structures (if applyable)
- Datamapping to other formats like: CSV, NEON, ...

## Datalists example:

```php
define("JSONplus_DATALIST_ROOT", __DIR__.'/'); #optional
```
```json
{"id":"userlist","options":<datalist:users>}
```

**JSONplus** will include the datalist *users* (located at `JSONplus_DATALIST_ROOT/users.json`).

This is short-hand for:
```json
{"id":"userlist","options":{"$ref":"./users.json#"}}
```

## JSONplus::worker

A simple worker script can handle several ways of processing a file.

```php
<?php
define("JSONplus_FILE_ARGUMENT", 'file'); #optional
define("JSONplus_POST_ARGUMENT", 'json'); #optional
require_once(__DIR__.'/JSONplus.php');
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
