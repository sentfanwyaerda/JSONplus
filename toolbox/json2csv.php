<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');

$json = JSONplus::worker('json');

header('Content-type: text/csv');
print JSONplus::export_csv($json);
?>
