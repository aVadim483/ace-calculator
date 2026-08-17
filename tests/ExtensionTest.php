<?php declare(strict_types=1);

namespace avadim\AceCalculator\Test;

use avadim\AceCalculator\AceCalculator;
use avadim\AceCalculator\Exception\ConfigException;
use PHPUnit\Framework\TestCase;

/**
 * Bundled extensions
 */
final class ExtensionTest extends TestCase
{
    /** @var AceCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AceCalculator();
    }

    public function testBoolOperators(): void
    {
        $this->calculator->loadExtension('Bool');

        self::assertEquals(1, $this->calculator->execute('1 < 2'));
        self::assertEquals(1, $this->calculator->execute('2 <= 2'));
        self::assertEquals(0, $this->calculator->execute('3 > 4'));
        self::assertEquals(1, $this->calculator->execute('4 >= 4'));
        self::assertEquals(1, $this->calculator->execute('5 == 5'));
        self::assertEquals(0, $this->calculator->execute('5 != 5'));
        self::assertEquals(0, $this->calculator->execute('1 && 0'));
        self::assertEquals(1, $this->calculator->execute('1 || 0'));
    }

    public function testBoolFunctions(): void
    {
        $this->calculator->loadExtension('Bool');

        self::assertEquals(23, $this->calculator->execute('if(100+20+3 > 111, 23, 34)'));
        self::assertEquals(34, $this->calculator->execute('if(1 > 2, 23, 34)'));

        self::assertEquals(-1, $this->calculator->execute('compare(10, 20)'));
        self::assertEquals(0, $this->calculator->execute('compare(10, 10)'));
        self::assertEquals(1, $this->calculator->execute('compare(20, 10)'));
        self::assertEquals(1, $this->calculator->execute('compare(20, 10, "gt")'));

        self::assertEquals(1, $this->calculator->execute('not(0)'));
        self::assertEquals(0, $this->calculator->execute('not(5)'));

        self::assertContains('if', $this->calculator->getFunctionList());
    }

    public function testBoolWithVariables(): void
    {
        $this->calculator
            ->loadExtension('Bool')
            ->setVar('$var1', 100)
            ->setVar('$var2', 200)
            ->calc('if($var1==100 || $var2==200, 1, 2)')
        ;

        self::assertEquals(1, $this->calculator->result());
    }

    public function testLoadExtensionIsIdempotent(): void
    {
        $this->calculator->loadExtension('Bool')->loadExtension('Bool');

        self::assertEquals(10, $this->calculator->execute('if(1 > 0, 10, 20)'));
    }

    public function testUnknownExtension(): void
    {
        $this->expectException(ConfigException::class);
        $this->calculator->loadExtension('NoSuchExtension');
    }

    public function testColorsExtension(): void
    {
        $this->calculator->loadExtension('Colors');

        self::assertSame('#ff007f', $this->calculator->execute('rgb(255, 0, 127)'));

        // a color may be converted to hsl and back
        $result = $this->calculator
            ->calc('rgb(255, 0, 127)', '$rgb')
            ->calc('hsl(hue($rgb), saturation($rgb), lightness($rgb))')
            ->result()
        ;
        self::assertSame('#ff007f', $result);

        self::assertSame('#b30059', $this->calculator->execute('color_darken("#ff007f", 15)'));
        self::assertSame('#ff66b2', $this->calculator->execute('color_lighten("#ff007f", 20)'));
        self::assertSame('#00ff80', $this->calculator->execute('color_complementary("#ff007f")'));
    }

    /**
     * The extension registers a token for hex color literals, so "#ff007f" can be written without quotes
     */
    public function testColorsHexLiteral(): void
    {
        $this->calculator->loadExtension('Colors');

        self::assertEquals(255, $this->calculator->execute('red(#ff007f)'));
        self::assertEquals(0, $this->calculator->execute('green(#ff007f)'));
        self::assertEquals(127, $this->calculator->execute('blue(#ff007f)'));
    }

    /**
     * "ColorsHexa" loads "Colors" and overrides its functions with the 8-digit (with alpha) notation
     */
    public function testColorsHexaExtension(): void
    {
        $this->calculator->loadExtension('ColorsHexa');

        self::assertSame('#ff007fff', $this->calculator->execute('rgb(255, 0, 127)'));
        self::assertSame('#ff007f80', $this->calculator->execute('rgba(255, 0, 127, 0.5)'));
        self::assertEqualsWithDelta(1.0, $this->calculator->execute('alpha("#ff007fff")'), 1e-9);
    }
}
