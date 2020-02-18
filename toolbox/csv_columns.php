<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');

$raw = JSONplus::worker('raw');

/*static*/ $column = array(); $primarykey_depth = -1; $multiple = TRUE;
/*todo*/ $keys = array( /*-1 => 'KEY'*/ );

header('Content-type: application/json');
$json = JSONplus::import_csv($raw);
$col = JSONplus::get_columns($json, $column, $primarykey_depth, $multiple, $keys);
print JSONplus::encode($col);
?>
