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

namespace avadim\AceCalculator\Extension\Colors;

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Token\TokenScalar;

/**
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalarHexString extends TokenScalar
{
    protected static $pattern = '/^\#[0-9a-f]+$/i';
    protected static $matching = self::MATCH_CALLBACK;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, $options = [])
    {
        parent::__construct($lexeme, $options);
        $this->lexeme = $lexeme;
    }

    /**
     * @param string $tokenStr
     * @param AbstractToken[] $prevTokens
     * @param array $allLexemes
     * @param int $lexemeNum
     *
     * @return bool
     */
    public static function isMatch(string $tokenStr, array $prevTokens, array $allLexemes, int &$lexemeNum)
    {
        if ($tokenStr === '#') {
            $i = 0;
            while (isset($allLexemes[$lexemeNum + $i + 1]) && preg_match(self::$pattern, $tokenStr . $allLexemes[$lexemeNum + $i + 1])) {
                ++$i;
                $tokenStr .= $allLexemes[$lexemeNum + $i];
            }
            if ($tokenStr !== '#') {
                $lexemeNum += $i; //isset($allLexemes[$lexemeNum + $i]) ? $i - 1 : $i;
                return $tokenStr;
            }
        }
        return false;
    }


}
