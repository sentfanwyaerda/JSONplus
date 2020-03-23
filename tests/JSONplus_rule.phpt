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
require __DIR__ . '/../JSONplus_schema_rule.php';

# Adjust PHP behavior and enable some Tester features (described later)
Tester\Environment::setup();

#------------------------------------------------#

$json = \JSONplus::decode(file_get_contents(__DIR__.'/../composer.json'));
$schema = new \JSONplus\Schema(__DIR__.'/../composer.schema');
$rule = new \JSONplus\Schema\Rule($schema);

Assert::same(array(), $rule->get_log());

//print_r($rule);
print 'EACH: '; var_dump(\JSONplus::interpret_error($rule->each($json, FALSE, '/'))); print PHP_EOL;
print 'LOG = '; print \JSONplus::encode($rule->get_log()); print PHP_EOL;

?>
