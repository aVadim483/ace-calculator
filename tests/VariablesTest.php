<?php declare(strict_types=1);

namespace avadim\AceCalculator\Test;

use avadim\AceCalculator\AceCalculator;
use avadim\AceCalculator\Exception\ExecException;
use PHPUnit\Framework\TestCase;

/**
 * Variables, identifiers and cascade calculations
 */
final class VariablesTest extends TestCase
{
    /** @var AceCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AceCalculator();
    }

    public function testVariables(): void
    {
        $result = $this->calculator
            ->setVar('$var1', 0.15)
            ->setVar('$var2', 0.22)
            ->calc('$var3 = $var1 + $var2')
            ->calc('$var3 * 20')
            ->result()
        ;

        self::assertEqualsWithDelta(7.4, $result, 1e-9);
        // the result variable
        self::assertEqualsWithDelta(7.4, $this->calculator->getVar('$_'), 1e-9);
        self::assertEqualsWithDelta(0.37, $this->calculator->getVar('$var3'), 1e-9);
        // the prefix "$" is optional in getVar()/setVar()
        self::assertEquals(0.22, $this->calculator->getVar('var2'));
        // an undefined variable
        self::assertNull($this->calculator->getVar('$var4'));
    }

    /**
     * Expressions separated by ";" are calculated one by one
     */
    public function testMultipleExpressions(): void
    {
        $result = $this->calculator->execute('$var1=0.15; $var2=0.22; $var3 = $var1 + $var2; $var3 * 20');

        self::assertEqualsWithDelta(7.4, $result, 1e-9);
        self::assertEqualsWithDelta(0.37, $this->calculator->getVar('$var3'), 1e-9);
    }

    public function testSetVars(): void
    {
        $this->calculator->setVars(['var1' => 0.15, 'var2' => 0.22]);
        self::assertEqualsWithDelta(0.37, $this->calculator->execute('$var1 + $var2'), 1e-9);

        // setVars() clears previous variables by default
        $this->calculator->setVars(['var1' => 1]);
        self::assertNull($this->calculator->getVar('$var2'));

        // ... unless the second argument is false
        $this->calculator->setVars(['var2' => 2], false);
        self::assertEquals(1, $this->calculator->getVar('$var1'));
        self::assertEquals(2, $this->calculator->getVar('$var2'));

        $this->calculator->removeVar('$var1');
        self::assertNull($this->calculator->getVar('$var1'));

        $this->calculator->removeVars();
        self::assertSame([], $this->calculator->getVars());
    }

    public function testResultVariable(): void
    {
        // the result may be assigned to a variable
        $this->calculator->execute('$var1 = 2 + 3', '$result');

        self::assertEquals(5, $this->calculator->getVar('$result'));
        self::assertEquals(5, $this->calculator->getVar('$_'));
        self::assertEquals(5, $this->calculator->result());
    }

    public function testUnknownVariable(): void
    {
        $this->expectException(ExecException::class);
        $this->calculator->execute('$undefined * 4');
    }

    /**
     * The parsed expression is cached, but variable values are not
     */
    public function testVariableValueIsNotCached(): void
    {
        $this->calculator->setVar('$x', 1);
        self::assertEquals(2, $this->calculator->execute('$x * 2'));

        $this->calculator->setVar('$x', 2);
        self::assertEquals(4, $this->calculator->execute('$x * 2'));

        $this->calculator->cacheEnable(false);
        $this->calculator->setVar('$x', 3);
        self::assertEquals(6, $this->calculator->execute('$x * 2'));
    }

    /**
     * Identifiers can be scalars, callbacks or expressions with other identifiers
     */
    public function testIdentifiers(): void
    {
        $this->calculator->setIdentifiers([
            'ONE'   => 1,
            'TWO'   => static function ($identifier) { return 2; },
            'THREE' => 'ONE + TWO',
        ]);

        self::assertEquals(3, $this->calculator->execute('ONE + TWO'));
        self::assertEquals(33, $this->calculator->execute('THREE * 11'));
        self::assertEquals(1, $this->calculator->getIdentifier('ONE'));

        $this->calculator->removeIdentifier('ONE');
        self::assertNull($this->calculator->getIdentifier('ONE'));
    }

    public function testUnknownIdentifier(): void
    {
        $this->expectException(ExecException::class);
        $this->calculator->execute('UNKNOWN + 1');
    }

    public function testMultipleExpressionsCanBeDisabled(): void
    {
        $this->calculator->setMultipleExpressionsEnable(false);
        self::assertFalse($this->calculator->getMultipleExpressionsEnable());

        // ";" is not a valid token when multiple expressions are disabled
        $this->expectException(\avadim\AceCalculator\Exception\LexerException::class);
        $this->calculator->execute('1 + 1; 2 + 2');
    }
}
