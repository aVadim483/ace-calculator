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

use avadim\AceCalculator\Generic\AbstractTokenScalar;
use avadim\AceCalculator\Generic\AbstractToken;

/**
 * Class TokenScalarNumber
 *
 * @package avadim\AceCalculator
 */
class TokenScalarHexNumber extends AbstractTokenScalar
{
    protected static $matching = self::MATCH_CALLBACK;

    /**
     * @param string           $tokenStr
     * @param AbstractToken[] $prevTokens
     * @param array           $allLexemes
     * @param int             $lexemeNum
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens, $allLexemes, &$lexemeNum)
    {
        return preg_match('/^0x[0-9a-f]+/i', $tokenStr);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return hexdec(substr($this->value, 2));
    }

}
