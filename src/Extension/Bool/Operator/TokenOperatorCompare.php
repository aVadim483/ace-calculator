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
    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
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
     * @throws \avadim\AceCalculator\Exception\CalcException
     * @throws \avadim\AceCalculator\Exception\LexerException
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $localStack = [$op1, $op2, static::$pattern];

        return $this->processor->callFunction('compare', $localStack);
    }

}