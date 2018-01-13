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

namespace avadim\AceCalculator\Token\Operator;

use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorDivide
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorDivide extends AbstractTokenOperator
{
    protected static $pattern = '/';

    /**
     * @return int
     */
    public function getPriority()
    {
        return 2;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     *
     * @throws \DivisionByZeroError
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        if ((float)$op2->getValue() === 0.0) {
            throw new \DivisionByZeroError('Divide a number by zero');
        }
        $result = $op1->getValue() / $op2->getValue();

        return new TokenScalarNumber($result);
    }
}
