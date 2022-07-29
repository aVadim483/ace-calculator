<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\Bool\Operator;

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;

use avadim\AceCalculator\Token\TokenScalar;

/**
 * Class TokenOperatorCompare
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorCompare extends AbstractTokenOperator
{
    const MATH_PRIORITY_COMPARE     = 0;
    const MATH_PRIORITY_AND         = -1;
    const MATH_PRIORITY_OR          = -1;

    protected $priority = self::MATH_PRIORITY_COMPARE;

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
     * @return TokenScalar
     *
     * @throws \avadim\AceCalculator\Exception\ExecException
     * @throws \avadim\AceCalculator\Exception\LexerException
     */
    public function execute(array &$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $localStack = [$op1, $op2, static::$pattern];

        return $this->getProcessor()->callFunction('compare', $localStack);
    }

}