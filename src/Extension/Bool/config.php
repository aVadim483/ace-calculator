<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

return [
    'include' => [
        'functions.php',
        'Operator/*.php',
    ],
    'operators' => [
        'gt'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorGt',
        'ge'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorGe',
        'lt'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorLt',
        'le'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorLe',
        'eq'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorEq',
        'ne'        => '\avadim\AceCalculator\Extension\Bool\Operator\TokenOperatorNe',
    ],
    'functions' => [
        'compare'   => ['\avadim\AceCalculator\Extension\Bool\compare', 2, true],
        'if'        => ['\avadim\AceCalculator\Extension\Bool\if_then', 3],
    ],
];
