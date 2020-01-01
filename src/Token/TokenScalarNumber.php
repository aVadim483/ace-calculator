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
     * @return string
     */
    public function getValue()
    {
        if (is_string($this->value)) {
            if (false !== strpos($this->value, '.')) {
                return (float)$this->value;
            }
            return (int)$this->value;
        }
        return $this->value;
    }

}
