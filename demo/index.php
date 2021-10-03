<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

error_reporting(E_ALL);

function source($file)
{
    $source = file_get_contents($file);
    echo '<hr>';
    highlight_string($source);
    echo '<hr>';

    include $file;
}

if (isset($_GET['demo']) && preg_match('/^[\w\-]+$/', $_GET['demo'])) {
    $demo = $_GET['demo'];
} else {
    $demo = 'simple';
}

echo '
    <a href="?demo=interactive">Interactive form</a>
    <a href="?demo=simple">Base usage</a>
    <a href="?demo=functions">Custom functions</a>
    <a href="?demo=operator">Custom operators</a>
    <a href="?demo=extension-bool">Extension usage</a>
    <a href="?demo=extension-colors">Extension "Colors" usage</a>
    <a href="?demo=extension-colors-hexa">Extension "ColorsHexa" usage</a>
';

$file = __DIR__ . '/demo.' . $demo . '.php';
if (!is_file($file)) {
    echo 'Demo file "demo.' . $demo . '.php" not found';
} else {
    if ($demo === 'interactive') {
        include $file;
    } else {
        source(__DIR__ . '/demo.' . $demo . '.php');
    }
}
