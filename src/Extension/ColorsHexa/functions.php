<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\ColorsHexa;

use avadim\AceColors\AceColors;

/**
 * @param $r
 * @param $g
 * @param $b
 *
 * @return string
 */
function rgb($r, $g, $b)
{
    return '#' . AceColors::rgbaToHexa([$r, $g, $b, 1]);
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
    return '#' . AceColors::rgbaToHexa([$r, $g, $b, $a]);
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
    return '#' . AceColors::hslaToHexa([$h, $s, $l, 1]);
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
    return '#' . AceColors::hslaToHexa([$h, $s, $l, $a]);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_red($hex, $value)
{
    return '#' . (new AceColors($hex))->setRed($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_green($hex, $value)
{
    return '#' . (new AceColors($hex))->setGreen($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_blue($hex, $value)
{
    return '#' . (new AceColors($hex))->setBlue($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_alpha($hex, $value)
{
    return '#' . (new AceColors($hex))->setAlpha($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_opacity($hex, $value)
{
    return color_alpha($hex, $value);
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_hue($hex, $value)
{
    return '#' . (new AceColors($hex))->setHue($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_saturation($hex, $value)
{
    return '#' . (new AceColors($hex))->setSaturation($value)->getHexa();
}

/**
 * @param string $hex
 * @param float|string $value
 *
 * @return string
 */
function color_lightness($hex, $value)
{
    return '#' . (new AceColors($hex))->setLightness($value)->getHexa();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_complementary($hex)
{
    return '#' . (new AceColors($hex))->complementary()->getHexa();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_invert($hex)
{
    return '#' . (new AceColors($hex))->invert()->getHexa();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_darken($hex, $value)
{
    return '#' . (new AceColors($hex))->darken($value)->getHexa();
}

/**
 * @param string $hex
 *
 * @return string
 */
function color_lighten($hex, $value)
{
    return '#' . (new AceColors($hex))->lighten($value)->getHexa();
}

// EOF