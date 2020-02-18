<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');

$json = JSONplus::worker('json');

/*static*/ $column = array(); $primarykey_depth = -1; $multiple = TRUE;
/*todo*/ $keys = array( /*-1 => 'KEY'*/ );

header('Content-type: text/markdown');
print JSONplus::export_markdown_table($json);
?>
