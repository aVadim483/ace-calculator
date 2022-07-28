<?php declare(strict_types=1);

namespace avadim\AceCalculator;

use PHPUnit\Framework\TestCase;

final class AceCalculatorTest extends TestCase
{
    /**
     * Expressions data provider
     */
    public function providerExpressions()
    {
        return [
            ['0.1 + 0.2'],
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

            ['1 + 2 * (2 - (4+10))^2 + sin(10)'],
            ['sin(10) * cos(50) / min(10, 20/2)'],

            ['100500 * 3.5E5'],
            ['100500 * 3.5E-5']
        ];
    }

    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating($expression)
    {
        $calculator = new AceCalculator();

        /** @var float $phpResult */
        $eval = str_replace('^', '**', $expression);
        eval('$phpResult = ' . $eval . ';');
        print $expression . ' = ' . $phpResult;
        $this->assertEquals($calculator->execute($expression), $phpResult);
    }

    public function testExponentiation()
    {
        $calculator = new AceCalculator();
        $this->assertEquals($calculator->execute('10 ^ 2'), 100);
    }

}
