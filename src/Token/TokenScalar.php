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

use avadim\AceCalculator\Generic\AbstractToken;

/**
 * Class TokenScalarString
 *
 * @package avadim\AceCalculator
 */
class TokenScalar extends AbstractToken
{
    public function __toString()
    {
        return (string)$this->getValue();
    }
}
