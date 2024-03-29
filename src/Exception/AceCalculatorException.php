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
 * Class AceCalculatorException
 *
 * @package avadim\AceCalculator
 */
abstract class AceCalculatorException extends \RuntimeException
{
    const CONFIG_OTHER_ERRORS       = 0;
    const CONFIG_OPERATOR_BAD_INTERFACE = 10;

    const LEXER_ERROR               = 20;
    const LEXER_UNKNOWN_TOKEN       = 21;
    const LEXER_UNKNOWN_FUNCTION    = 22;

    const CALC_ERROR                = 30;
    const CALC_UNKNOWN_VARIABLE     = 31;
    const CALC_UNKNOWN_IDENTIFIER   = 32;
    const CALC_INCORRECT_EXPRESSION = 33;
    const CALC_WRONG_FUNC_ARGS      = 34;
    const CALC_ERROR_OPERATOR       = 35;
    const CALC_INCORRECT_IDENTIFIER_EXPR = 36;

    /**
     * @param int $code
     * @param array|null $args
     *
     * @return void
     */
    public static function call(int $code, ?array $args = [])
    {
        switch ($code) {
            case self::CALC_INCORRECT_IDENTIFIER_EXPR:
                $message = sprintf('Cannot define identifier "%" (incorrect expression or callback)', ...$args);
                $class = ExecException::class;
                break;
            default:
                $message = 'AceCalculator Runtime Exception';
                $class = self::class;
        }
        throw new $class($message, $code);
    }

}
