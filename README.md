# JSONplus
*JSONplus* is a simple PHP-class extending the default JSON processing with *datalist*s and standard pretty printing.

```php
define("JSONplus_DATALIST_ROOT", dirname(__FILE__).'/');

JSONplus::decode($json, TRUE); #json_decode($json, TRUE);
JSONplus::encode($value); #json_encode($value);
```
