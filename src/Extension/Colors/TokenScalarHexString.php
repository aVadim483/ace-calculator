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
use avadim\AceCalculator\Generic\AbstractTokenScalar;

/**
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalarHexString extends AbstractTokenScalar
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
     * @param string          $lexeme
     * @param AbstractToken[] $prevTokens
     * @param array           $allLexemes
     * @param int             $lexemeNum
     *
     * @return bool
     */
    public static function isMatch($lexeme, $prevTokens, $allLexemes, &$lexemeNum)
    {
        if ($lexeme === '#') {
            $i = 0;
            while (isset($allLexemes[$lexemeNum + $i + 1]) && preg_match(self::$pattern, $lexeme . $allLexemes[$lexemeNum + $i + 1])) {
                ++$i;
                $lexeme .= $allLexemes[$lexemeNum + $i];
            }
            if ($lexeme !== '#') {
                $lexemeNum += $i; //isset($allLexemes[$lexemeNum + $i]) ? $i - 1 : $i;
                return $lexeme;
            }
        }
        return false;
    }


}
