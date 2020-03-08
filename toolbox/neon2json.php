<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');
require_once(dirname(dirname(__FILE__)).'/vendor/autoload.php');

$raw = \JSONplus::worker('raw');
header('Content-type: application/json');
$json = \JSONplus::import_neon($raw);
print \JSONplus::encode($json);
?>
