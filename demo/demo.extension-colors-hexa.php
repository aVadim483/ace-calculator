<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->loadExtension('ColorsHexa');

$color = $calculator->execute('rgba(255, 0, 127, "80%")');

$the_same = $calculator
    ->calc('rgba(255, 0, 127, 0.8)', '$rgba')  // calc expression and save result in $rgb
    ->calc('hsla(hue($rgba), saturation($rgba), lightness($rgba), alpha($rgba))')
    ->result();

$darken = $calculator->execute('color_darken("#ff007fcc")');

$lighten = $calculator->execute('color_lighten("#ff007fcc")');

$complementary = $calculator
    ->calc('color_complementary(#ff007fcc)', '$new')
    ->result();

$red    = $calculator->execute('red(#ff007fcc)');
$green  = $calculator->execute('green(#ff007fcc)');
$blue   = $calculator->execute('blue(#ff007fcc)');
$alpha  = $calculator->execute('alpha(#ff007fcc)');

echo "<table>
<tr><td>color:          <td></td><td>$color</td></tr>
<tr><td>the same color: <td></td><td>$the_same</td></tr>
<tr><td>darken:         <td></td><td>$darken</td></tr>
<tr><td>lighten:        <td></td><td>$lighten</td></tr>
<tr><td>complementary:  <td></td><td>$complementary</td></tr>
<tr><td>red channel:    <td></td><td>$red</td></tr>
<tr><td>green channel:  <td></td><td>$green</td></tr>
<tr><td>blue channel:   <td></td><td>$blue</td></tr>
<tr><td>alpha channel:  <td></td><td>$alpha</td></tr>
</table>";