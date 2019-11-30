<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/ace-calculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

function source($file)
{
    $source = file_get_contents($file);
    echo '<hr>';
    echo '<pre>', htmlspecialchars($source), '<pre>';
    echo '<hr>';
    include $file;
}

if (isset($_GET['demo']) && preg_match('/^[\w\-]+$/', $_GET['demo'])) {
    $demo = $_GET['demo'];
} else {
    $demo = 'simple';
}

echo '
    <a href="?demo=simple">Base usage</a>
    <a href="?demo=operator">Custom operator</a>
    <a href="?demo=extension-bool">Extension usage</a>
    <a href="?demo=extension-colors">Extension "Colors" usage</a>
    <a href="?demo=extension-colors-hexa">Extension "ColorsHexa" usage</a>
';

$file = __DIR__ . '/demo.' . $demo . '.php';
if (!is_file($file)) {
    echo 'Demo file "demo.' . $demo . '.php" not found';
} else {
    source(__DIR__ . '/demo.' . $demo . '.php');
}
