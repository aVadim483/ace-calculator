<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * Based on NeonXP/MathExecutor by Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Token;

use avadim\AceCalculator\Generic\AbstractToken;

/**
 * Class TokenIdentifier
 *
 * @package avadim\AceCalculator
 */
class TokenIdentifier extends AbstractToken
{
    protected static $pattern = '/^[a-zA-Z_\x7f-\xff]([a-zA-Z0-9_\x7f-\xff]*)$/';
    protected static $matching = self::MATCH_REGEX;

}
