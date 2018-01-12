<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 * Based on NeonXP/MathExecutor by Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Token;

use avadim\AceCalculator\Generic\AbstractTokenGroup;

/**
 * Class TokenLeftBracket
 *
 * @package avadim\AceCalculator
 */
class TokenLeftBracket extends AbstractTokenGroup
{
    protected static $pattern = '(';

}
