<?php

include __DIR__ . '/../src/autoload.php';

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalarNumber;

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
     * Association of this operator (self::LEFT_ASSOC or self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Execution of this operator
     * @param AbstractToken[] $stack Stack of tokens
     *
     * @return TokenScalarNumber
     */
    public function execute(array &$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() % $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->addOperator('mod', '\TokenOperatorModulus');
$calculator->addFunction('strlen', static function ($val) {
    return strlen($val);
});

$result1 = $calculator->execute('286 mod 100');
$result2 = $calculator->execute('strlen("qwerty")');

echo "result1: $result1 <br>\n";
echo "result2: $result2 <br>\n";
