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
