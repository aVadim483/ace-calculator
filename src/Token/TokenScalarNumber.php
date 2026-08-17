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
class TokenScalarNumber extends TokenScalar
{
    protected static $matching = self::MATCH_NUMERIC;

    /**
     * @return int|float
     */
    public function getValue()
    {
        if (is_string($this->value) && is_numeric($this->value)) {
            // "12" becomes an integer, "1.2" and "1e-3" become floats
            return $this->value + 0;
        }
        return $this->value;
    }

}
