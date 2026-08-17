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
        self::assertEquals(16, $this->calculator->execute('abs(0x10)'));
    }

    /**
     * A number in the exponential notation must not be truncated to an integer,
     * even where the value of the token is taken as is (arguments of functions, a single literal)
     */
    public function testScientificNotation(): void
    {
        self::assertEqualsWithDelta(0.001, $this->calculator->execute('abs(1e-3)'), 1e-12);
        self::assertEqualsWithDelta(0.001, $this->calculator->execute('min(1e-3, 5)'), 1e-12);
        self::assertEqualsWithDelta(0.001, $this->calculator->execute('(1e-3)'), 1e-12);
        self::assertEqualsWithDelta(0.002, $this->calculator->execute('2 * 1e-3'), 1e-12);
        self::assertEqualsWithDelta(0.0015, $this->calculator->execute('abs(1.5e-3)'), 1e-12);

        // integers stay integers
        self::assertSame(3, $this->calculator->execute('1 + 2'));
        self::assertSame(10, $this->calculator->execute('(10)'));
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

    /**
     * An expression with no result must not be a fatal error
     */
    public function testEmptyBrackets(): void
    {
        $this->expectException(ExecException::class);
        $this->calculator->execute('()');
    }

    /**
     * ";" separates expressions, but not inside a string literal
     */
    public function testSemicolonInsideString(): void
    {
        $this->calculator->addFunction('strlength', static function ($value) {
            return strlen((string)$value);
        });

        self::assertEquals(3, $this->calculator->execute('strlength("a;b")'));
        self::assertEquals(3, $this->calculator->execute('$s = "a;b"; strlength($s)'));
    }

    /**
     * Empty parts between the separators are skipped
     */
    public function testExtraSemicolons(): void
    {
        self::assertEquals(4, $this->calculator->execute('1 + 1;; 2 + 2;'));

        $this->expectException(ExecException::class);
        $this->calculator->execute(';');
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
