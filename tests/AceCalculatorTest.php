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

            ['1 + 2 * (2 - (4+10))^2 + sin(10)'],
            ['sin(10) * cos(50) / min(10, 20/2)'],

            ['100500 * 3.5e5'],
            ['100500 * 3.5E-5'],

            ['abs(-4.2)'],
            ['cos(PI)', 'cos(M_PI)'],
            ['tn(M_PI_4)', 'tan(M_PI / 4)'],
            ['M_SQRT2', 'sqrt(2)'],
        ];
    }

    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating($calcExpression, $evalExpression = null)
    {
        $calculator = new AceCalculator();

        /** @var float $phpResult */
        $eval = str_replace('^', '**', $evalExpression ?: $calcExpression);
        eval('$phpResult = ' . $eval . ';');
        print $calcExpression . ' = ' . $phpResult;
        $this->assertEquals($calculator->execute($calcExpression), $phpResult);
    }


    public function testExponentiation()
    {
        $calculator = new AceCalculator();
        $this->assertEquals($calculator->execute('10 ^ 2'), 100);
    }


    public function testVariables()
    {
        $calculator = new AceCalculator();
        $r1 = $calculator->execute('$var1=0.15; $var2=0.22; $var3 = $var1 + $var2; $var3 * 20');
        $r2 = $calculator
            ->setVar('$var1', 0.15)
            ->setVar('$var2', 0.22)
            ->calc('$var3 = $var1 + $var2')
            ->calc('$var3 * 20')
            ->result()
        ;
        $this->assertEquals($r1, 7.4);
        $this->assertEquals($r2, 7.4);
        // result variable
        $this->assertEquals($calculator->getVar('$_'), 7.4);
        // user variable
        $this->assertEquals($calculator->getVar('$var1'), 0.15);
        // variable without '$'
        $this->assertEquals($calculator->getVar('var2'), 0.22);
        $this->assertEquals($calculator->getVar('$var3'), 0.37);
        // undefined variable
        $this->assertEquals($calculator->getVar('$var4'), null);
    }

    /**
     * Multiple nested identifiers
     *
     * @return void
     */
    public function testIdentifiers()
    {
        $calculator = new AceCalculator();
        $calculator->setIdentifiers([
            'ONE' => 1,
            'TWO' => function($identifier) { return 2; },
            'THREE' => 'ONE + TWO',
        ]);

        $this->assertEquals($calculator->execute('THREE * 11'), 33);
    }


    public function testBool()
    {
        $calculator = new AceCalculator();
        $calculator->loadExtension('Bool');
        $this->assertEquals($calculator->execute('if(100+20 > 111, 23, 34)'), 23);
        $calculator
            ->setVar('$var1', 100)
            ->setVar('$var2', 200)
        ;
        $calculator->calc('if($var1==100 || $var2==200, 1, 2)');
        $this->assertEquals($calculator->result(), 1);
    }

}
