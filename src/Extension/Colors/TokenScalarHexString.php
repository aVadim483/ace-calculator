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

use avadim\AceCalculator\Generic\AbstractTokenScalar;

/**
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalarHexString extends AbstractTokenScalar
{
    protected static $pattern = '/^\#[0-9,a-f]+$/i';
    protected static $matching = self::MATCH_REGEX;
    protected static $lexemes_max = 2;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, $options = [])
    {
        parent::__construct($lexeme, $options);
        $this->lexeme = $lexeme;
    }

}
