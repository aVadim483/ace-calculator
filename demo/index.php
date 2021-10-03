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

$menu = [
    ['label' => 'Interactive form', 'href' => '?demo=interactive'],
    ['label' => 'Base usage', 'href' => '?demo=simple'],
    ['label' => 'Variables', 'href' => '?demo=variables'],
    ['label' => 'Custom functions', 'href' => '?demo=functions'],
    ['label' => 'Custom operators', 'href' => '?demo=operator'],
    ['label' => 'Extension usage', 'href' => '?demo=extension-bool'],
    ['label' => 'Extension "Colors" usage', 'href' => '?demo=extension-colors'],
    ['label' => 'Extension "ColorsHexa" usage', 'href' => '?demo=extension-colors-hexa'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AceCalculator Demo</title>
    <style>
        td {vertical-align: top;}
    </style>
</head>
<body>
<table>
    <tr>
        <td>
            <ul style="padding-right: 10px; margin-right: 10px; border-right: 1px solid #555555;">
                <?php
                foreach ($menu as $item) {
                    echo '<li><a href="' . $item['href'] . '">' . $item['label'] . '</a></li>';
                }
                ?>
            </ul>
        </td>
        <td>
            <?php
            if (isset($_GET['demo']) && preg_match('/^[\w\-]+$/', $_GET['demo'])) {
                $demo = $_GET['demo'];
            } else {
                $demo = 'simple';
            }

            $file = __DIR__ . '/demo.' . $demo . '.php';
            if (!is_file($file)) {
                echo 'Demo file "demo.' . $demo . '.php" not found';
            } else {
                if ($demo === 'interactive') {
                    include $file;
                } else {
                    ini_set("highlight.comment", "#777777");
                    source(__DIR__ . '/demo.' . $demo . '.php');
                }
            }
            ?>
        </td>
    </tr>
</table>
</body>
</html>
