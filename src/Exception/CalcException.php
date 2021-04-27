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

namespace avadim\AceCalculator\Exception;

/**
 * Class CalcException
 *
 * @package avadim\AceCalculator
 */
class CalcException extends AceCalculatorException
{
    protected $errorMessage;

    protected $errorExpression;

    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    public function setErrorExpression($expression)
    {
        $this->errorExpression = $expression;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorExpression()
    {
        return $this->errorExpression;
    }
}
