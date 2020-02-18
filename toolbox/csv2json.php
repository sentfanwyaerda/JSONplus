<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');

$raw = JSONplus::worker('raw');

header('Content-type: application/json');
$json = JSONplus::import_csv($raw);
print JSONplus::encode($json);
?>
