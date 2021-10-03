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

    /**
     * @param $message
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    /**
     * @param $expression
     */
    public function setErrorExpression($expression)
    {
        $this->errorExpression = $expression;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorExpression()
    {
        return $this->errorExpression;
    }
}
