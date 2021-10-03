<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->loadExtension('Colors');

$color = $calculator
    ->execute('rgb(255, 0, 127)');

$the_same = $calculator
    ->calc('rgb(255, 0, 127)', '$rgb')  // calc expression and save result in $rgb
    ->calc('hsl(hue($rgb), saturation($rgb), lightness($rgb))')
    ->result();

$darken = $calculator
    ->execute('color_darken("#ff007f", 15)');

$lighten = $calculator
    ->execute('color_lighten("#ff007f", 20)');

$complementary = $calculator
    ->execute('color_complementary("#ff007f")');

echo "<table>
<tr><td>color:          <td></td><td>$color</td><td style='background-color: $color;'>&nbsp;</td></tr>
<tr><td>the same color: <td></td><td>$the_same</td><td style='background-color: $the_same;'>&nbsp;</td></tr>
<tr><td>darken:         <td></td><td>$darken</td><td style='background-color: $darken;'>&nbsp;</td></tr>
<tr><td>lighten:        <td></td><td>$lighten</td><td style='background-color: $lighten;'>&nbsp;</td></tr>
<tr><td>complementary:  <td></td><td>$complementary</td><td style='background-color: $complementary;'>&nbsp;</td></tr>
</table>";