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

/**
 * Class TokenScalarNumber
 *
 * @package avadim\AceCalculator
 */
class TokenScalarHexNumber extends TokenScalar
{
    protected static $matching = self::MATCH_CALLBACK;

    /**
     * @param string    $tokenStr
     * @param array     $prevTokens
     * @param array     $allLexemes
     * @param int       $lexemeNum
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens, $allLexemes, &$lexemeNum)
    {
        return preg_match('/^0x[0-9a-f]+/i', $tokenStr);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return hexdec(substr($this->value, 2));
    }

    /**
     * @return int
     */
    public function getValueNum()
    {
        return $this->getValue();
    }

}
