<?php

include __DIR__ . '/../src/autoload.php';

$calculator = new avadim\AceCalculator\AceCalculator();

$expression = isset($_POST['expression']) ? $_POST['expression'] : '';
$result = '';
$error = '';
if ($expression) {
// calc expression
    try {
    $result = $calculator->execute($expression);
    } catch (\avadim\AceCalculator\Exception\ExecException $e) {
        $error = $e->getErrorMessage();
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ace Calculator</title>
    <style>
        td { vertical-align: top; padding: 10px;}
    </style>
</head>
<body>
<table>
    <tr>
        <td>
            <form method="post">
                <h3>Expression</h3>
                <textarea name="expression" style="width:640px; height: 80px;"><?=$expression;?></textarea><br><br>
                <button type="submit">Calculate</button>
            </form>
            <div>
                <?php if ($expression) {
                    echo '<p></p><b>Result</b><br>', $result, '</p>';
                }
                if ($error) {
                    echo '<p><b>Error</b><br>', $error, '</p>';
                }
                ?>
            </div>
        </td>
        <td>
            <h3>Available functions</h3>
            <?php echo implode('<br>', $calculator->getFunctionList()) ?>
        </td>
    </tr>
</table>
</body>
</html>
