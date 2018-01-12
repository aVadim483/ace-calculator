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

use avadim\AceCalculator\Generic\AbstractTokenScalar;
use avadim\AceCalculator\Generic\AbstractToken;

/**
 * Class TokenScalarNumber
 *
 * @package avadim\AceCalculator
 */
class TokenScalarNumber extends AbstractTokenScalar
{
    protected static $matching = self::MATCH_NUMERIC;

    /**
     * @param string           $tokenStr
     * @param AbstractToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return is_numeric($tokenStr);
    }
}
