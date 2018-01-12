<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\Colors;

use avadim\PhpColors\PhpColors;

include_once __DIR__ . '/PhpColors.php';

/**
 * @param $r
 * @param $g
 * @param $b
 *
 * @return string
 */
function rgb($r, $g, $b)
{
    return '#' . PhpColors::rgbToHex([$r, $g, $b]);
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
    return '#' . PhpColors::rgbToHex([$r, $g, $b, $a]);
}

function hsl($h, $s, $l)
{
    return '#' . PhpColors::hslToHex([$h, $s, $l]);
}

function hsla($h, $s, $l, $a)
{
    return '#' . PhpColors::hslaToHex([$h, $s, $l, $a]);
}

/**
 * @param $hex
 *
 * @return float
 */
function red($hex)
{
    return (new PhpColors($hex))->red;
}

/**
 * @param $hex
 *
 * @return float
 */
function green($hex)
{
    return (new PhpColors($hex))->grenn;
}

/**
 * @param $hex
 *
 * @return float
 */
function blue($hex)
{
    return (new PhpColors($hex))->blue;
}

/**
 * @param $hex
 *
 * @return float
 */
function alpha($hex)
{
    return (new PhpColors($hex))->alpha;
}

/**
 * @param $hex
 *
 * @return int
 */
function hue($hex)
{
    return (new PhpColors($hex))->hue;
}

/**
 * @param $hex
 *
 * @return float
 */
function saturation($hex)
{
    return (new PhpColors($hex))->saturation;
}

/**
 * @param $hex
 *
 * @return float
 */
function lightness($hex)
{
    return (new PhpColors($hex))->lightness;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_red($hex, $value)
{
    return '#' . (new PhpColors($hex))->setRed($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_green($hex, $value)
{
    return '#' . (new PhpColors($hex))->setGreen($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_blue($hex, $value)
{
    return '#' . (new PhpColors($hex))->setBlue($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_alpha($hex, $value)
{
    return '#' . (new PhpColors($hex))->setAlpha($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_hue($hex, $value)
{
    return '#' . (new PhpColors($hex))->setHue($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_saturation($hex, $value)
{
    return '#' . (new PhpColors($hex))->setSaturation($value)->hex;
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_lightness($hex, $value)
{
    return '#' . (new PhpColors($hex))->setLightness($value)->hex;
}

/**
 * @param string $hex
 *
 * @return string
 */
function complementary($hex)
{
    return '#' . (new PhpColors($hex))->complementary();
}

/**
 * @param string $hex
 *
 * @return string
 */
function darken($hex)
{
    return '#' . (new PhpColors($hex))->darken();
}

/**
 * @param string $hex
 *
 * @return string
 */
function lighten($hex)
{
    return '#' . (new PhpColors($hex))->lighten();
}

// EOF