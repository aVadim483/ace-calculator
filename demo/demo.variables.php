<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();

//assign value to variable $x
$calculator->setVar('$x', 100);
// calc expression with $x and assign result to $y
$calculator->execute('min(1,-sin($x),cos($x)-0.5)', '$y');

// calc expression with variables
$calculator->execute('1 + 2 * (2 - (4+10))^2 + ($sin = sin($x)) + $y');

// cascade calculation
// result of any previous calculation store in variable $_
$calculator
    ->calc('PI + $x * ($y - $_)^2 + $sin')
    ->calc('$_ + sin(10)')
    ->result();

foreach ($calculator->getVars() as $name => $value) {
    echo "$name: $value <br>\n";
}
