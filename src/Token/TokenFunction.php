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

use avadim\AceCalculator\Generic\AbstractTokenScalar;
use avadim\AceCalculator\Exception\CalcException;

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
     * @return AbstractTokenScalar
     *
     * @throws CalcException
     */
    public function execute(&$stack)
    {
        $args = [];
        $token = null;
        list($name, $numArguments, $callback, $variableArguments) = $this->options;
        for ($i = 0; $i < $numArguments; $i++) {
            $token = $stack ? array_pop($stack) : null;
            if ($token instanceof AbstractTokenScalar || $token instanceof TokenIdentifier) {
                $args[] = $token->getValue();
            } elseif (is_scalar($token)) {
                $args[] = $token;
            } else {
                throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
            }
        }
        if ($variableArguments) {
            while ($stack && ($token = array_pop($stack))) {
                if (!$token instanceof TokenLeftBracket) {
                    $args[] = $token->getValue();
                } else {
                    $stack[] = $token;
                    break;
                }
            }
        } elseif ($stack) {
            $token = array_pop($stack);
            if (!$token instanceof TokenLeftBracket) {
                throw new CalcException('Wrong arguments of function "' . $name . '"', CalcException::CALC_WRONG_FUNC_ARGS);
            }
        }
        $result = call_user_func_array($callback, array_reverse($args));

        return $this->calculator->getTokenFactory()->createScalarToken($result);
    }
}
