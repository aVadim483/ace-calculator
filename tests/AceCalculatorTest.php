<?php declare(strict_types=1);

namespace avadim\AceCalculator\Test;

use avadim\AceCalculator\AceCalculator;
use avadim\AceCalculator\Exception\DivisionByZeroException;
use avadim\AceCalculator\Exception\ExecException;
use avadim\AceCalculator\Exception\LexerException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Base arithmetic, default functions and error handling
 */
final class AceCalculatorTest extends TestCase
{
    /** @var AceCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AceCalculator();
    }

    /**
     * Expressions which must give the same result as native PHP
     *
     * @return array[] [expression, php equivalent (optional)]
     */
    public static function providerExpressions(): array
    {
        return [
            ['0.1 + 0.2 - 0.3'],
            ['1 + 2'],

            ['0.1 - 0.2'],
            ['1 - 2'],

            ['0.1 * 2'],
            ['1 * 2'],

            ['0.1 / 0.2'],
            ['1 / 2'],

            ['2 * 2 + 3 * 3'],

            ['1 + 0.6 - 3 * 2 / 50'],

            ['(5 + 3) * -1'],

            ['2+2*2'],
            ['(2+2)*2'],
            ['(2+2)*-2'],
            ['(2+-2)*2'],
            ['+3 - 5'],

            ['1 + 2 * (2 - (4+10))^2 + sin(10)'],
            ['sin(10) * cos(50) / min(10, 20/2)'],

            ['2 ^ 3 ^ 2'],

            ['100500 * 3.5e5'],
            ['100500 * 3.5E-5'],
            ['1e-3'],

            ['abs(-4.2)'],
            ['cos(PI)', 'cos(M_PI)'],
            ['tn(M_PI_4)', 'tan(M_PI / 4)'],
            ['M_SQRT2', 'sqrt(2)'],
        ];
    }

    /**
     * The attribute is used by PHPUnit 10+, the annotation - by PHPUnit 9
     * (on PHP 7.4 the attribute below is just a comment)
     *
     * @dataProvider providerExpressions
     *
     * @param string $expression
     * @param string|null $phpExpression
     */
    #[DataProvider('providerExpressions')]
    public function testCalculating(string $expression, ?string $phpExpression = null): void
    {
        $php = str_replace('^', '**', $phpExpression !== null ? $phpExpression : $expression);
        $expected = eval('return ' . $php . ';');

        self::assertEqualsWithDelta($expected, $this->calculator->execute($expression), 1e-9, $expression);
    }

    public function testExponentiation(): void
    {
        self::assertEquals(100, $this->calculator->execute('10 ^ 2'));
        // "^" is right associative, as "**" in PHP
        self::assertEquals(512, $this->calculator->execute('2 ^ 3 ^ 2'));
    }

    /**
     * Unary operators have a higher priority than power,
     * so "-2 ^ 2" is (-2) ^ 2 and not -(2 ^ 2) as in PHP
     */
    public function testUnaryOperators(): void
    {
        self::assertEquals(4, $this->calculator->execute('-2 ^ 2'));
        self::assertEquals(-8, $this->calculator->execute('(5 + 3) * -1'));
        self::assertEquals(-2, $this->calculator->execute('+3 - 5'));
    }

    public function testHexNumbers(): void
    {
        self::assertEquals(14, $this->calculator->execute('0x10 / 4 + 10'));
    }

    public function testDefaultConstants(): void
    {
        self::assertEqualsWithDelta(M_PI, $this->calculator->execute('PI'), 1e-9);
        self::assertEqualsWithDelta(M_E, $this->calculator->execute('E'), 1e-9);
        // any standard PHP math constant is available too
        self::assertEqualsWithDelta(M_SQRT3, $this->calculator->execute('M_SQRT3'), 1e-9);
    }

    /**
     * Functions with a fixed number of arguments
     */
    public function testFunctionsWithSeveralArguments(): void
    {
        self::assertEquals(3, $this->calculator->execute('intdiv(10, 3)'));
        self::assertEqualsWithDelta(1.0, $this->calculator->execute('fmod(10, 3)'), 1e-9);
        self::assertEqualsWithDelta(5.0, $this->calculator->execute('hypot(3, 4)'), 1e-9);
        self::assertEqualsWithDelta(M_PI / 4, $this->calculator->execute('atan2(1, 1)'), 1e-9);
    }

    /**
     * Functions with a variable number of arguments
     */
    public function testFunctionsWithVariableArguments(): void
    {
        self::assertEquals(1, $this->calculator->execute('min(3, 1, 2)'));
        self::assertEquals(3, $this->calculator->execute('max(3, 1, 2)'));
        self::assertEquals(2, $this->calculator->execute('avg(1, 2, 3)'));
        self::assertEquals(3.14, $this->calculator->execute('round(3.14159, 2)'));
        self::assertEquals(3, $this->calculator->execute('round(3.14159)'));
        self::assertEqualsWithDelta(1.0, $this->calculator->execute('log(M_E)'), 1e-9);
        self::assertEqualsWithDelta(3.0, $this->calculator->execute('log(8, 2)'), 1e-9);
    }

    public function testFunctionList(): void
    {
        $functions = $this->calculator->getFunctionList();

        self::assertContains('sqrt', $functions);
        self::assertContains('round', $functions);
        // the list is sorted
        $sorted = $functions;
        sort($sorted);
        self::assertSame($sorted, $functions);
    }

    public function testEmptyExpression(): void
    {
        $this->expectException(ExecException::class);
        $this->calculator->execute('');
    }

    public function testUnknownToken(): void
    {
        $this->expectException(LexerException::class);
        $this->calculator->execute('2 @ 3');
    }

    public function testUnknownFunction(): void
    {
        $this->expectException(LexerException::class);
        $this->calculator->execute('nosuchfunction(1)');
    }

    public function testIncorrectBrackets(): void
    {
        $this->expectException(LexerException::class);
        $this->calculator->execute('(2 + 2');
    }

    public function testAssignWithoutVariable(): void
    {
        $this->expectException(LexerException::class);
        $this->calculator->execute('5 = 6');
    }

    public function testDivisionByZero(): void
    {
        $this->expectException(DivisionByZeroException::class);
        $this->calculator->execute('10 / 0');
    }

    public function testProcessorLog(): void
    {
        $this->calculator->execute('2 + 2 * 2');

        self::assertSame(
            [
                '* ( 2, 2) => 4',
                '+ ( 2, 4) => 6',
            ],
            $this->calculator->getProcessor()->getLog(true)
        );
    }
}
