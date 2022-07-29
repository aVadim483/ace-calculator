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

use avadim\AceCalculator\Exception\ExecException;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorMultiply
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorMultiply extends AbstractTokenOperator
{
    protected static $pattern = '*';

    protected $priority = self::MATH_PRIORITY_MULTIPLY;

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
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
     * @throws ExecException
     */
    public function execute(array &$stack)
    {
        if (count($stack) < 2) {
            throw new ExecException('Operator "*" (multiply) error', ExecException::CALC_ERROR_OPERATOR);
        }
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValueNum() * $op2->getValueNum();

        return new TokenScalarNumber($result);
    }
}
