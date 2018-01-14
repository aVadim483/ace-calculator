<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

return [
    'extensions' => [
        'Colors',
    ],
    'include' => [
        'functions.php',
    ],
    'functions' => [
        'rgb'   => ['\avadim\AceCalculator\Extension\ColorsHexa\rgb', 3],
        'rgba'  => ['\avadim\AceCalculator\Extension\ColorsHexa\rgba', 4],
        'hsl'   => ['\avadim\AceCalculator\Extension\ColorsHexa\hsl', 3],
        'hsla'  => ['\avadim\AceCalculator\Extension\ColorsHexa\hsla', 4],

        'color_red'         => ['\avadim\AceCalculator\Extension\ColorsHexa\color_red', 2],
        'color_green'       => ['\avadim\AceCalculator\Extension\ColorsHexa\color_green', 2],
        'color_blue'        => ['\avadim\AceCalculator\Extension\ColorsHexa\color_blue', 2],
        'color_alpha'       => ['\avadim\AceCalculator\Extension\ColorsHexa\color_alpha', 2],
        'color_hue'         => ['\avadim\AceCalculator\Extension\ColorsHexa\color_hue', 2],
        'color_saturation'  => ['\avadim\AceCalculator\Extension\ColorsHexa\color_saturation', 2],
        'color_lightness'   => ['\avadim\AceCalculator\Extension\ColorsHexa\color_lightness', 2],

        'color_complementary'   => ['\avadim\AceCalculator\Extension\ColorsHexa\color_complementary', 1],
        'color_invert'          => ['\avadim\AceCalculator\Extension\ColorsHexa\color_invert', 1],
        'color_darken'          => ['\avadim\AceCalculator\Extension\ColorsHexa\color_darken', 1],
        'color_lighten'         => ['\avadim\AceCalculator\Extension\ColorsHexa\color_lighten', 1],
    ],
];
