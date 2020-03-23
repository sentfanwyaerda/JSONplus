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
require __DIR__ . '/../JSONplus.php';

# Adjust PHP behavior and enable some Tester features (described later)
Tester\Environment::setup();

#------------------------------------------------#

$file = __DIR__.'/../composer.json';
$composer = new \JSONplus();
$composer->open($file);
$raw = file_get_contents($file);
$json = new \JSONplus($raw);

//print_r($composer);
//print_r($json);

# TESTS __toString()
Assert::same(\JSONplus::encode(json_decode($raw, TRUE)), (string) $json);
Assert::same((string) $composer, (string) $json);

Assert::type('array', $composer->__toArray());
Assert::notSame(array(), $composer->__toArray());

print_r($json->encode());

?>
