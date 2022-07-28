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
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorMinus
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorMinus extends AbstractTokenOperator
{
    protected static $pattern = '-';

    protected $priority = self::MATH_PRIORITY_MINUS;

    private $unary = false;

    /**
     * @param string $lexeme
     * @param array $options
     */
    public function __construct($lexeme, array $options = [])
    {
        parent::__construct($lexeme, $options);
        if (!empty($options['begin'])) {
            $this->unary = true;
        }
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        if ($this->unary) {
            return self::MATH_PRIORITY_UNARY;
        }
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        if ($this->unary) {
            return self::RIGHT_ASSOC;
        }
        return self::LEFT_ASSOC;
    }

    /**
     * @param AbstractToken[] $stack
     *
     * @return TokenScalarNumber
     * @throws CalcException
     */
    public function execute(array &$stack)
    {
        if ($this->unary) {
            if (count($stack) < 1) {
                throw new CalcException('Operator "-" (minus) error', CalcException::CALC_ERROR_OPERATOR);
            }
            $op = array_pop($stack);
            $result = -$op->getValue();
        } else {
            if (count($stack) < 2) {
                throw new CalcException('Operator "minus" error', CalcException::CALC_ERROR_OPERATOR);
            }
            $op2 = array_pop($stack);
            $op1 = array_pop($stack);
            $result = $op1->getValueNum() - $op2->getValueNum();
        }

        return new TokenScalarNumber($result);
    }
}
