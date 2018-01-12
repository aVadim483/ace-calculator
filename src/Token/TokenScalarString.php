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

/**
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalarString extends AbstractTokenScalar
{
    protected static $pattern = '/^\"[^\"]*\"$/';
    protected static $matching = self::MATCH_REGEX;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, $options = [])
    {
        $value = (string)substr($lexeme, 1, -1);
        parent::__construct($value, $options);
        $this->lexeme = $lexeme;
    }

}
