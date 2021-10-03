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

namespace avadim\AceCalculator;

use avadim\AceCalculator\Generic\AbstractTokenOperator;

use avadim\AceCalculator\Token\TokenFunction;
use avadim\AceCalculator\Token\TokenIdentifier;
use avadim\AceCalculator\Token\TokenLeftBracket;
use avadim\AceCalculator\Token\TokenScalar;
use avadim\AceCalculator\Token\TokenVariable;

use avadim\AceCalculator\Exception\CalcException;

/**
 * Class Processor
 *
 * @package avadim\AceCalculator
 */
class Processor
{
    /**
     * @var Container
     */
    private $container;

    private $functions = [];
    private $logEnable = false;
    private $log = [];

    /**
     * Processor constructor.
     *
     * @param Container $container
     */
    public function __construct($container = null)
    {
        $this->logEnable(true);
        $this->setContainer($container);
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return $this->container->get('TokenFactory');
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function logEnable($flag)
    {
        $this->logEnable = (bool)$flag;

        return $this;
    }

    /**
     * @param bool $beautify
     *
     * @return array
     */
    public function getLog($beautify = false)
    {
        if ($beautify) {
            $result = [];
            foreach($this->log as $item) {
                $result[] = $item[0] . ' ( ' . implode(', ', $item[1]) . ') => ' . $item[2];
            }
            return $result;
        }
        return $this->log;
    }

    /**
     * @param TokenFunction|TokenIdentifier|AbstractTokenOperator $token
     * @param array $stack
     * @param bool $return
     *
     * @return TokenScalar
     *
     * @throws CalcException
     */
    protected function executeToken($token, &$stack, $return = false)
    {
        $oldStack = $stack;
        if (method_exists($token, 'execute')) {
            $result = $token->execute($stack);
        } else {
            $result = $this->getTokenFactory()->createScalarToken($token->getValue());
        }
        if (!$return) {
            $stack[] = $result;
        }
        if ($this->logEnable) {
            $args = [];
            $count = count($oldStack);
            for ($i = 0; $i < $count; $i++) {
                if (isset($stack[$i]) && $stack[$i] === $oldStack[$i]) {
                    continue;
                }
                if (is_scalar($oldStack[$i])) {
                    $args[] = $oldStack[$i];
                } else {
                    $args[] = $oldStack[$i]->getValue();
                }
            }
            $tokenStr = (string)$token->getValue();
            $this->log[] = [$tokenStr, $args, $result->getValue()];
        }
        return $result;
    }

    /**
     * Calculate array of tokens in reverse polish notation
     *
     * @param  array $tokens      Array of tokens
     * @param  array $variables   Array of variables
     * @param  array $identifiers Array of identifiers
     *
     * @return int|float
     *
     * @throws CalcException
     */
    public function calculate($tokens, array $variables = [], array $identifiers = [])
    {
        $stack = [];
        /*
        $tokensArray = [];
        foreach ($tokens as $token) {
            $tokensArray[] = is_scalar($token) ? $token : $token->getValue();
        }
        */
        foreach ($tokens as $token) {
            if ($token instanceof TokenFunction) {
                $this->executeToken($token, $stack);
            } elseif ($token instanceof AbstractTokenOperator) {
                if (empty($stack)) {
                    throw new CalcException('Incorrect expression ', CalcException::CALC_INCORRECT_EXPRESSION);
                }
                $this->executeToken($token, $stack);
            } elseif ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
            } elseif ($token instanceof TokenScalar) {
                $stack[] = $token;
            } elseif ($token instanceof TokenIdentifier) {
                $identifier = $token->getValue();
                if (isset($identifiers[$identifier])) {
                    if (is_callable($identifiers[$identifier])) {
                        $token = $this->getTokenFactory()->createScalarToken(call_user_func($identifiers[$identifier], $variables, $identifiers));
                    } elseif (is_object($identifiers[$identifier])) {
                        $token = $this->executeToken($token, $stack);
                    } elseif (is_scalar($identifiers[$identifier])) {
                        $token = $this->getTokenFactory()->createScalarToken($identifiers[$identifier]);
                    }
                } else {
                    throw new CalcException('Unknown identifier "' . $identifier . '"', CalcException::CALC_UNKNOWN_VARIABLE);
                }
                $stack[] = $token;
            } elseif ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if ($token->assignVariable) {
                    $stack[] = $variable;
                } else {
                    if (!$variables || !array_key_exists($variable, $variables)) {
                        throw new CalcException('Unknown variable "' . $variable . '"', CalcException::CALC_UNKNOWN_VARIABLE);
                    }
                    $value = $variables[$variable];
                    $stack[] = $this->getTokenFactory()->createScalarToken($value);
                }
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new CalcException('Incorrect expression ', CalcException::CALC_INCORRECT_EXPRESSION);
        }

        return $result->getValue();
    }

    /**
     * @param $name
     * @param $stack
     *
     * @return TokenScalar
     *
     * @throws Exception\LexerException
     * @throws CalcException
     */
    public function callFunction($name, &$stack)
    {
        if (!isset($this->functions[$name])) {
            $this->functions[$name] = $this->getTokenFactory()->createFunction($name);
        }
        return $this->executeToken($this->functions[$name], $stack, true);
    }

}
