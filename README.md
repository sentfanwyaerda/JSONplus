# JSONplus
*JSONplus* is a simple PHP-class extending the default JSON processing with *datalist*s and standard pretty printing.

```php
define("JSONplus_DATALIST_ROOT", dirname(__FILE__));

$value = JSONplus::decode($json, TRUE);	#json_decode($json, TRUE);
$json  = JSONplus::encode($value);	#json_encode($value);
```

###Example:
```json
{"id":"userlist","options":<datalist:users>}
```
*JSONplus* will include the datalist *users* (located in ``JSONplus_DATALIST_ROOT/users.json``).
