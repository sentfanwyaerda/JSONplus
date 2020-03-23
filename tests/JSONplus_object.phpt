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
require dirname(__DIR__).'/src/JSONplus.php';

# Adjust PHP behavior and enable some Tester features (described later)
Tester\Environment::setup();

#------------------------------------------------#

$file = __DIR__.'/../composer.json';
$schemafile = __DIR__.'/../draft-07.schema';

$raw = file_get_contents($file);
$compare = json_decode($raw, TRUE);


# TESTS initial with json-string
$json = new \JSONplus($raw);
Assert::same(\JSONplus::NOT_FOUND_ERROR, $json->get_uri());
Assert::same($compare, $json->get());
//print_r_($json, 'json');


# TESTS initial with uri-string
//$composer = new \JSONplus(); $composer->open($file);
$composer = new \JSONplus($file);
Assert::same($file, $composer->get_uri());
Assert::same($compare, $composer->get());
//print_r_($composer, 'composer');

Assert::same((array) $composer, (array) \JSONplus::file($file));
//print_r_(\JSONplus::file($file), '::file');


# TESTS ERRORcodes
Assert::false(\JSONplus::NOT_FOUND_ERROR === \JSONplus::MALFORMED_ERROR);
Assert::type('array', \JSONplus::EMPTY);

# TESTS __toString()
Assert::same(\JSONplus::encode($compare), (string) $json);
Assert::same((string) $composer, (string) $json);
Assert::same((string) $composer, $composer->__toString());
Assert::same((string) $composer, $composer->export());
//print_r_($json->export(), 'json->export');

# TESTS __toArray()
Assert::type('array', $composer->__toArray());
Assert::notSame(array(), $composer->__toArray());
# Assert::same((array) $composer, $composer->__toArray()); // PHP doesn't support (array) likewise (string)
//print_r_((array) $composer, 'toArray');
//print_r_($composer->__toArray(), 'toArray');

# TESTS Class recursion
/*
$recur = new \JSONplus\JSON($composer);
print_r_($recur, 'recursion');
print_r_($recur->get_uri(), 'uri');
print_r_((string) $recur, 'str');
print_r_($recur->get_extension(), 'extension');
print_r_($composer->get_extension(), 'extension');

$schema = new \JSONplus\Schema($schemafile);
print_r_($schema, 'schema');
print_r_($schema->get_uri(), 'uri');
print_r_($schema->get_extension(), 'extension');
*/
?>
