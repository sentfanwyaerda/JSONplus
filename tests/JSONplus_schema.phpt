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
require __DIR__ . '/../JSONplus_schema.php';

# Adjust PHP behavior and enable some Tester features (described later)
Tester\Environment::setup();

#------------------------------------------------#

# find_schema_for
Assert::false(\JSONplus\Schema::find_schema_for('abcdef-example.json', __DIR__, FALSE, FALSE));
Assert::same('example.schema', \JSONplus\Schema::find_schema_for('abcdef-example.json', __DIR__, FALSE, TRUE));

# Elements
## Array
Assert::true(\JSONplus\Schema::element_validate_array(json_decode('[]', TRUE), json_decode('{"type":"array"}', TRUE)));
Assert::false(\JSONplus\Schema::element_validate_array("example", json_decode('{"type":"string"}', TRUE)));
Assert::false(\JSONplus\Schema::element_validate_array(TRUE, json_decode('{"type":"boolean"}', TRUE)));

## String
Assert::true(\JSONplus\Schema::element_validate_string("example", json_decode('{"type":"string"}', TRUE)));

?>
