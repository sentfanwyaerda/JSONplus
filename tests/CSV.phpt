<?php

use Tester\Assert;

# Load Tester library
if(file_exists(__DIR__ . '/../vendor/autoload.php')){
  require __DIR__ . '/../vendor/autoload.php';       # installation by Composer, where JSONplus is the main project
} elseif(file_exists(__DIR__ . '/../../../../autoload.php')){
  require __DIR__ . '/../../../../autoload.php';       # installation by Composer, where JSONplus is part of a vendor collection of a third project
}
# Load the tested class. Composer or your autoloader surely takes
# care of that in practice.
require __DIR__ . '/../src/JSONplus_CSV.php';

# Adjust PHP behavior and enable some Tester features (described later)
Tester\Environment::setup();

#------------------------------------------------#

Assert::true(TRUE);
$uri = __DIR__.'/../bulk/GR2018-partijz.csv';
$csv = new \JSONplus\CSV($uri);

//print_r_($csv, 'CSV');
//var_dump($csv);

$json = new \JSONplus\JSON($csv);

//print_r_((string) $json, 'json');

?>
