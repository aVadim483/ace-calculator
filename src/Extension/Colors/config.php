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
    ],
    'tokens' => [
        'hex_string'        => '\avadim\AceCalculator\Extension\Colors\TokenScalarHexString',
    ],
    'functions' => [
        'rgb'   => ['\avadim\AceCalculator\Extension\Colors\rgb', 3],
        'rgba'  => ['\avadim\AceCalculator\Extension\Colors\rgba', 4],
        'hsl'   => ['\avadim\AceCalculator\Extension\Colors\hsl', 3],
        'hsla'  => ['\avadim\AceCalculator\Extension\Colors\hsla', 4],

        'red'           => ['\avadim\AceCalculator\Extension\Colors\red', 1],
        'green'         => ['\avadim\AceCalculator\Extension\Colors\green', 1],
        'blue'          => ['\avadim\AceCalculator\Extension\Colors\blue', 1],
        'alpha'         => ['\avadim\AceCalculator\Extension\Colors\alpha', 1],
        'hue'           => ['\avadim\AceCalculator\Extension\Colors\hue', 1],
        'saturation'    => ['\avadim\AceCalculator\Extension\Colors\saturation', 1],
        'lightness'     => ['\avadim\AceCalculator\Extension\Colors\lightness', 1],

        'color_red'             => ['\avadim\AceCalculator\Extension\Colors\color_red', 2],
        'color_green'           => ['\avadim\AceCalculator\Extension\Colors\color_green', 2],
        'color_blue'            => ['\avadim\AceCalculator\Extension\Colors\color_blue', 2],
        'color_alpha'           => ['\avadim\AceCalculator\Extension\Colors\color_alpha', 2],
        'color_hue'             => ['\avadim\AceCalculator\Extension\Colors\color_hue', 2],
        'color_saturation'      => ['\avadim\AceCalculator\Extension\Colors\color_saturation', 2],
        'color_lightness'       => ['\avadim\AceCalculator\Extension\Colors\color_lightness', 2],

        'color_complementary'   => ['\avadim\AceCalculator\Extension\Colors\color_complementary', 1],
        'color_invert'          => ['\avadim\AceCalculator\Extension\Colors\color_invert', 1],
        'color_darken'          => ['\avadim\AceCalculator\Extension\Colors\color_darken', 2],
        'color_lighten'         => ['\avadim\AceCalculator\Extension\Colors\color_lighten', 2],
        'color_saturate'        => ['\avadim\AceCalculator\Extension\Colors\color_saturate', 2],
        'color_desaturate'      => ['\avadim\AceCalculator\Extension\Colors\color_desaturate', 2],
    ],
];
