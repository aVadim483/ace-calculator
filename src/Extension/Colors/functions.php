<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\Colors;

use avadim\AceColors\AceColors;

include_once __DIR__ . '/AceColors.php';

/**
 * @param $r
 * @param $g
 * @param $b
 *
 * @return string
 */
function rgb($r, $g, $b)
{
    return '#' . AceColors::rgbToHex([$r, $g, $b]);
}

/**
 * @param $r
 * @param $g
 * @param $b
 * @param $a
 *
 * @return string
 */
function rgba($r, $g, $b, $a)
{
    return '#' . AceColors::rgbToHex([$r, $g, $b, $a]);
}

/**
 * @param $h
 * @param $s
 * @param $l
 *
 * @return string
 */
function hsl($h, $s, $l)
{
    return '#' . AceColors::hslToHex([$h, $s, $l]);
}

/**
 * @param $h
 * @param $s
 * @param $l
 * @param $a
 *
 * @return string
 */
function hsla($h, $s, $l, $a)
{
    return '#' . AceColors::hslaToHex([$h, $s, $l, $a]);
}

/**
 * @param $hex
 *
 * @return float
 */
function red($hex)
{
    return (new AceColors($hex))->red;
}

/**
 * @param $hex
 *
 * @return float
 */
function green($hex)
{
    return (new AceColors($hex))->green;
}

/**
 * @param $hex
 *
 * @return float
 */
function blue($hex)
{
    return (new AceColors($hex))->blue;
}

/**
 * @param $hex
 *
 * @return float
 */
function alpha($hex)
{
    return (new AceColors($hex))->alpha;
}

/**
 * @param $hex
 *
 * @return float
 */
function opacity($hex)
{
    return alpha($hex);
}

/**
 * @param $hex
 *
 * @return int
 */
function hue($hex)
{
    return (new AceColors($hex))->hue;
}

/**
 * @param $hex
 *
 * @return float
 */
function saturation($hex)
{
    return (new AceColors($hex))->saturation;
}

/**
 * @param $hex
 *
 * @return float
 */
function lightness($hex)
{
    return (new AceColors($hex))->lightness;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_red($hex, $value)
{
    return (string)(new AceColors($hex))->setRed($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_green($hex, $value)
{
    return (string)(new AceColors($hex))->setGreen($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_blue($hex, $value)
{
    return (string)(new AceColors($hex))->setBlue($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_alpha($hex, $value)
{
    return (string)(new AceColors($hex))->setAlpha($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_hue($hex, $value)
{
    return (string)(new AceColors($hex))->setHue($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_saturation($hex, $value)
{
    return (string)(new AceColors($hex))->setSaturation($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_desaturation($hex, $value)
{
    if (is_numeric($value)) {
        $value = -$value;
    } elseif ($value) {
        $value = '-(' . $value . ')';
    }
    return (string)(new AceColors($hex))->setSaturation($value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_lightness($hex, $value)
{
    return (string)(new AceColors($hex))->setLightness($value);
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_complementary($hex)
{
    return (string)(new AceColors($hex))->complementary();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_invert($hex)
{
    return (string)(new AceColors($hex))->invert();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_darken($hex, $value)
{
    return (string)(new AceColors($hex))->darken($value);
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_lighten($hex, $value)
{
    return (string)(new AceColors($hex))->lighten($value);
}

// EOF