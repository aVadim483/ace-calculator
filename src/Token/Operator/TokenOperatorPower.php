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

namespace avadim\AceCalculator\Token\Operator;

use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorPower
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorPower extends AbstractTokenOperator
{
    protected static $pattern = '^';

    /**
     * @return int
     */
    public function getPriority()
    {
        return 3;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::RIGHT_ASSOC;
    }

    /**
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() ** $op2->getValue();

        return new TokenScalarNumber($result);
    }
}
