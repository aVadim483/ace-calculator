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

namespace avadim\AceCalculator\Token;

use avadim\AceCalculator\Exception\AceCalculatorException;
use avadim\AceCalculator\Exception\ExecException;

/**
 * Class TokenFunction
 *
 * @package avadim\AceCalculator
 */
class TokenFunction extends TokenIdentifier
{
    /**
     * @param array $stack
     *
     * @return TokenScalar
     *
     * @throws ExecException
     */
    public function execute(array &$stack)
    {
        $args = [];
        list($name, $numArguments, $callback, $variableArguments) = $this->options;
        for ($i = 0; $i < $numArguments; $i++) {
            $token = $stack ? array_pop($stack) : null;
            if ($token instanceof TokenScalar || $token instanceof TokenIdentifier) {
                $args[] = $token->getValue();
            }
            elseif (is_scalar($token)) {
                $args[] = $token;
            }
            else {
                $error = sprintf('Wrong arguments of function "%s()" (%d expected)', $name, $numArguments);
                throw new ExecException($error, AceCalculatorException::CALC_WRONG_FUNC_ARGS);
            }
        }
        if ($variableArguments) {
            while ($stack && ($token = array_pop($stack)) && !$token instanceof TokenLeftBracket) {
                $args[] = $token->getValue();
            }
        } elseif ($stack) {
            $token = array_pop($stack);
            if (!$token instanceof TokenLeftBracket) {
                $error = sprintf('Wrong arguments of function "%s()" (%d expected)', $name, $numArguments);
                throw new ExecException($error, AceCalculatorException::CALC_WRONG_FUNC_ARGS);
            }
        }
        $result = call_user_func_array($callback, array_reverse($args));

        return $this->getProcessor()->getTokenFactory()->createScalarToken($result);
    }
}
