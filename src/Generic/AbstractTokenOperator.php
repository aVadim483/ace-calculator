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

namespace avadim\AceCalculator\Generic;

/**
 * Class AbstractTokenOperator
 *
 * @package avadim\AceCalculator
 */
abstract class AbstractTokenOperator extends AbstractToken
{
    const RIGHT_ASSOC   = 'RIGHT';
    const LEFT_ASSOC    = 'LEFT';

    const MATH_PRIORITY_UNARY       = 4;
    const MATH_PRIORITY_POWER       = 3;
    const MATH_PRIORITY_MULTIPLY    = 2;
    const MATH_PRIORITY_DIVIDE      = 2;
    const MATH_PRIORITY_PLUS        = 1;
    const MATH_PRIORITY_MINUS       = 1;
    const MATH_PRIORITY_ASSIGN      = -5;

    /**
     * @return int
     */
    abstract public function getPriority();

    /**
     * @return string
     */
    abstract public function getAssociation();

    /**
     * @param array $stack
     *
     * @return mixed
     */
    abstract public function execute(&$stack);

    /**
     * @param $token
     *
     * @return bool
     */
    public function lowPriority($token)
    {
        if (
            ($token instanceof AbstractTokenOperator)
            &&
            (
                ($this->getAssociation() === AbstractTokenOperator::LEFT_ASSOC && $this->getPriority() <= $token->getPriority())
                ||
                ($this->getAssociation() === AbstractTokenOperator::RIGHT_ASSOC && $this->getPriority() < $token->getPriority())
            )
        ) {
            return true;
        }
        return false;
    }

}
