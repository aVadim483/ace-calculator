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
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalarString extends TokenScalar
{
    protected static $pattern = '/^\"[^\"]*\"$/';
    protected static $matching = self::MATCH_REGEX;

    /**
     * @param mixed $lexeme
     * @param array $options
     */
    public function __construct($lexeme, array $options = [])
    {
        if (is_string($lexeme) && $lexeme !== '' && ($lexeme[0] === '"' || $lexeme[0] === '\'') && ($lexeme[0] === substr($lexeme, -1))) {
            $value = (string)substr($lexeme, 1, -1);
        } else {
            $value = (string)$lexeme;
        }
        parent::__construct($value, $options);
        $this->lexeme = $lexeme;
    }

}
