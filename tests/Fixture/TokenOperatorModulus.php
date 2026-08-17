<?php declare(strict_types=1);

namespace avadim\AceCalculator\Test\Fixture;

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Custom operator defined as a class
 */
class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    protected $priority = self::MATH_PRIORITY_POWER;

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
     */
    public function execute(array &$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);

        return new TokenScalarNumber($op1->getValue() % $op2->getValue());
    }
}
