<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();

// New function hypotenuse() with 2 arguments
$calculator->addFunction('hypotenuse', function ($a, $b) {
    return sqrt($a ^ 2 + $b ^ 2);
}, 2);

// New function round()
//      1 - minimum number of arguments
//      true - variable number of arguments
$calculator->addFunction('round', function ($a, $b = 0) {
    return round($a, $b);
}, 1, true);

echo $calculator->execute('hypotenuse(3,4)'), "<br>\n";
echo $calculator->execute('round(hypotenuse(3,4))'), "<br>\n";
echo $calculator->execute('round(hypotenuse(3,4), 2)'), "<br>\n";
