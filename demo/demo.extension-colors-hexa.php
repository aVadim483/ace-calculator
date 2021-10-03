<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->loadExtension('ColorsHexa');

$color = $calculator->execute('rgba(255, 0, 127, "80%")', '$color');

$the_same = $calculator
    ->calc('rgba(255, 0, 127, 0.8)', '$rgba')  // calc expression and save result in $rgb
    ->calc('hsla(hue($rgba), saturation($rgba), lightness($rgba), alpha($rgba))')
    ->result();

$darken = $calculator->execute('color_darken($color, 15)');

$lighten = $calculator->execute('color_lighten($color, 20)');

$complementary = $calculator->execute('color_complementary($color)');

$red    = $calculator->execute('red(#1f007fcc)');
$green  = $calculator->execute('green(#1f007fcc)');
$blue   = $calculator->execute('blue(#1f007fcc)');
$alpha  = $calculator->execute('alpha(#1f007fcc)');

echo "<table>
<tr><td>color:          <td></td><td>$color</td><td style='background-color: $color;'>&nbsp;</td></tr>
<tr><td>the same color: <td></td><td>$the_same</td><td style='background-color: $the_same;'>&nbsp;</td></tr>
<tr><td>darken:         <td></td><td>$darken</td><td style='background-color: $darken;'>&nbsp;</td></tr>
<tr><td>lighten:        <td></td><td>$lighten</td><td style='background-color: $lighten;'>&nbsp;</td></tr>
<tr><td>complementary:  <td></td><td>$complementary</td><td style='background-color: $complementary;'>&nbsp;</td></tr>
<tr><td>red channel:    <td></td><td>$red</td></tr>
<tr><td>green channel:  <td></td><td>$green</td></tr>
<tr><td>blue channel:   <td></td><td>$blue</td></tr>
<tr><td>alpha channel:  <td></td><td>$alpha</td></tr>
</table>";
