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

namespace avadim\Tests;

use avadim\AceCalculator\AceCalculator;

class MathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating($expression)
    {
        $calculator = new AceCalculator();

        /** @var float $phpResult */
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($calculator->execute($expression), $phpResult);
    }

    public function testExponentiation()
    {
        $calculator = new AceCalculator();
        $this->assertEquals($calculator->execute('10 ^ 2'), 100);
    }

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

            ['sin(10) * cos(50) / min(10, 20/2)'],

            ['100500 * 3.5E5'],
            ['100500 * 3.5E-5']
        ];
    }
}
