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

$jsonfile = __DIR__.'/table.json';
$raw = file_get_contents($jsonfile);

$schemafile = __DIR__.'/../draft-07.schema';
$sraw = file_get_contents($schemafile);

# Testing JSONplus::decode
Assert::same(json_decode($raw, TRUE), \JSONplus::decode($raw, TRUE));  # we expect the same
Assert::same(json_decode($sraw, TRUE), \JSONplus::decode($sraw, TRUE));  # we expect the same

# Testing JSONplus::encode
Assert::same(json_decode($raw, TRUE), \JSONplus::decode(\JSONplus::encode(\JSONplus::decode($raw, TRUE)),TRUE));  # we expect the same
Assert::same(json_decode($sraw, TRUE), \JSONplus::decode(\JSONplus::encode(\JSONplus::decode($sraw, TRUE)),TRUE));  # we expect the same

# Testing ID Table
Assert::same('f09a3e00626b959617dc42bbe268c63e', md5(\JSONplus::encode(\JSONplus::ID_table(\JSONplus::decode($sraw, TRUE)))) );

# Is table
Assert::true(\JSONplus::is_table(\JSONplus::decode($raw, TRUE)));
Assert::false(\JSONplus::is_table(\JSONplus::decode($sraw, TRUE)));

?>
