<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');
require_once(dirname(dirname(__FILE__)).'/vendor/autoload.php');

$json = JSONplus::worker('json');
header('Content-type: text/neon');
print JSONplus::export_neon($json);
?>
