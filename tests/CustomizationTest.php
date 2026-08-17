<?php declare(strict_types=1);

namespace avadim\AceCalculator\Test;

use avadim\AceCalculator\AceCalculator;
use avadim\AceCalculator\Test\Fixture\TokenOperatorModulus;
use avadim\AceCalculator\Token\Operator\TokenOperator;
use PHPUnit\Framework\TestCase;

/**
 * Custom functions and operators, options and error handlers
 */
final class CustomizationTest extends TestCase
{
    /** @var AceCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AceCalculator();
    }

    public function testAddFunction(): void
    {
        $this->calculator->addFunction('dummy', static function ($a) {
            return $a * 2;
        });
        self::assertEquals(246, $this->calculator->execute('dummy(123)'));

        // a function with a fixed number of arguments
        $this->calculator->addFunction('hypotenuse', static function ($a, $b) {
            return sqrt($a * $a + $b * $b);
        }, 2);
        self::assertEqualsWithDelta(5.0, $this->calculator->execute('hypotenuse(3, 4)'), 1e-9);

        // a function with optional arguments
        $this->calculator->addFunction('nround', static function ($a, $b = 0) {
            return round($a, (int)$b);
        }, 1, true);
        self::assertEquals(3.14, $this->calculator->execute('nround(3.14159, 2)'));
        self::assertEquals(4, $this->calculator->execute('nround(3.7)'));

        self::assertContains('hypotenuse', $this->calculator->getFunctionList());
    }

    public function testFunctionWithStringArgument(): void
    {
        $this->calculator->addFunction('strlength', static function ($value) {
            return strlen((string)$value);
        });

        self::assertEquals(6, $this->calculator->execute('strlength("qwerty")'));
    }

    /**
     * A simple way to add an operator - priority and callback
     */
    public function testAddOperatorAsCallback(): void
    {
        $this->calculator->addOperator('mod', [TokenOperator::MATH_PRIORITY_DIVIDE, static function (array &$stack) {
            $op2 = array_pop($stack);
            $op1 = array_pop($stack);

            return $op1->getValue() % $op2->getValue();
        }]);

        self::assertEquals(86, $this->calculator->execute('286 mod 100'));
    }

    /**
     * An alternative way - a class of the operator token
     */
    public function testAddOperatorAsClass(): void
    {
        $this->calculator->addOperator('mod', TokenOperatorModulus::class);

        self::assertEquals(86, $this->calculator->execute('286 mod 100'));
        // MATH_PRIORITY_POWER is higher than "+"
        self::assertEquals(87, $this->calculator->execute('1 + 286 mod 100'));
    }

    public function testDivisionByZeroHandler(): void
    {
        $this->calculator->setDivisionByZeroHandler(static function ($a, $b) {
            return 0;
        });

        self::assertEquals(0, $this->calculator->execute('10 / 0'));
        self::assertIsCallable($this->calculator->getDivisionByZeroHandler());
    }

    public function testUnknownIdentifierHandler(): void
    {
        $this->calculator->setIdentifiers(['ONE' => 1, 'TWO' => 2]);

        // the name of the identifier as a string
        $this->calculator->setUnknownIdentifierHandler(static function ($identifier) {
            return $identifier;
        });
        self::assertSame('THREE', $this->calculator->execute('THREE'));

        // the result of another expression
        $calculator = $this->calculator;
        $calculator->setUnknownIdentifierHandler(static function ($identifier) use ($calculator) {
            return $calculator->execute('ONE + TWO');
        });
        self::assertEquals(3, $calculator->execute('THREE'));
    }

    public function testUnknownVariableHandler(): void
    {
        // any undefined variable is interpreted as 0
        $this->calculator->setUnknownVariableHandler(static function () {
            return 0;
        });

        self::assertEquals(0, $this->calculator->execute('$undefined * 4'));
    }

    /**
     * The handler gets the calculator, the name of the variable and all defined variables
     */
    public function testUnknownVariableHandlerArguments(): void
    {
        $received = [];
        $this->calculator->setVar('$known', 1);
        $this->calculator->setUnknownVariableHandler(static function ($calculator, $variable, $variables) use (&$received) {
            $received = [$calculator, $variable, $variables];

            return 0;
        });
        $this->calculator->execute('$undefined');

        self::assertInstanceOf(AceCalculator::class, $received[0]);
        self::assertSame('$undefined', $received[1]);
        self::assertArrayHasKey('$known', $received[2]);
    }

    /**
     * Non-numeric values cause a warning in arithmetic operations...
     */
    public function testNonNumericStrict(): void
    {
        $this->calculator->setVar('$x', null);

        $warnings = [];
        set_error_handler(static function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;

            return true;
        }, E_USER_WARNING);
        try {
            $result = $this->calculator->execute('$x * 12');
        } finally {
            restore_error_handler();
        }

        self::assertEquals(0, $result);
        self::assertNotEmpty($warnings);
        self::assertStringContainsString('non-numeric value', $warnings[0]);
    }

    /**
     * ... unless the option "non_numeric" is set
     */
    public function testNonNumericIgnore(): void
    {
        $this->calculator->setOption('non_numeric', true);
        $this->calculator->setVar('$x', null);

        $warnings = [];
        set_error_handler(static function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;

            return true;
        }, E_USER_WARNING);
        try {
            $result = $this->calculator->execute('$x * 12');
        } finally {
            restore_error_handler();
        }

        self::assertEquals(0, $result);
        self::assertSame([], $warnings);
        self::assertTrue($this->calculator->getOption('non_numeric'));
    }
}
