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
    ->execute('darken("#ff007f")');

$lighten = $calculator
    ->execute('lighten("#ff007f")');

$complementary = $calculator
    ->execute('complementary("#ff007f")');

echo "<table>
<tr><td>color:          <td></td><td>$color</td></tr>
<tr><td>the same color: <td></td><td>$the_same</td></tr>
<tr><td>darken:         <td></td><td>$darken</td></tr>
<tr><td>lighten:        <td></td><td>$lighten</td></tr>
<tr><td>complementary:  <td></td><td>$complementary</td></tr>
</table>";