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
 * Class TokenOperatorMinus
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorMinus extends AbstractTokenOperator
{
    protected static $pattern = '-';

    private $unary = false;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, $options = [])
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
            return 4;
        }
        return 1;
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
     */
    public function execute(&$stack)
    {
        if ($this->unary) {
            $op = array_pop($stack);
            $result = -$op->getValue();
        } else {
            $op2 = array_pop($stack);
            $op1 = array_pop($stack);
            $result = $op1->getValue() - $op2->getValue();
        }

        return new TokenScalarNumber($result);
    }
}
