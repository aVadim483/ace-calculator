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

use avadim\AceCalculator\Exception\CalcException;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Token\TokenScalar;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorPower
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorAssign extends AbstractTokenOperator
{
    protected static $pattern = '=';

    /**
     * @return int
     */
    public function getPriority()
    {
        return self::MATH_PRIORITY_ASSIGN;
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
     * @return TokenScalar
     *
     * @throws CalcException
     */
    public function execute(array &$stack)
    {
        if (count($stack) < 2) {
            throw new CalcException('Operator "=" (assign) error', CalcException::CALC_ERROR_OPERATOR);
        }
        $variable = array_pop($stack);
        $value = array_pop($stack);
        $this->container->get('Calculator')->setVar($variable, $value->getValue());

        return new TokenScalar($value->getValue());
    }
}
