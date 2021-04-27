<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();

// calc expression
$result1 = $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)+0');

// calc expression with variable
$calculator->setVar('$x', 100);
$result2 = $calculator->execute('min(1,-sin($x),cos($x)-0.5)');

// cascade calculation
$result3 = $calculator
    ->calc('0x10 / 4 +10')  // you can use hexadecimal numbers
    ->calc('1 + 2 * (2 - $_)^2')
    ->calc('$_ + sin(10)')
    ->result();

echo "result1: $result1 \n";
echo "result2: $result2 \n";
echo "result3: $result3 \n";
