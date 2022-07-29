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

use avadim\AceCalculator\Exception\UnknownIdentifier;
use avadim\AceCalculator\Exception\UnknownVariable;
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;

use avadim\AceCalculator\Token\TokenFunction;
use avadim\AceCalculator\Token\TokenIdentifier;
use avadim\AceCalculator\Token\TokenLeftBracket;
use avadim\AceCalculator\Token\TokenScalar;
use avadim\AceCalculator\Token\TokenVariable;

use avadim\AceCalculator\Exception\ExecException;

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

    private $handlers = [];

    /**
     * Processor constructor.
     *
     * @param Container|null $container
     */
    public function __construct(Container $container = null)
    {
        $this->logEnable(true);
        $this->setContainer($container);
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
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
    public function logEnable(bool $flag)
    {
        $this->logEnable = (bool)$flag;

        return $this;
    }

    /**
     * @param bool|null $beautify
     *
     * @return array
     */
    public function getLog(?bool $beautify = false)
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
     * @param bool|null $return
     *
     * @return TokenScalar
     *
     * @throws ExecException
     */
    protected function executeToken($token, array &$stack, ?bool $return = false)
    {
        $oldStack = $stack;
        if (method_exists($token, 'execute')) {
            $value = $token->execute($stack);
        }
        else {
            $value = $token->getValue();
        }
        if ($value instanceof AbstractToken) {
            $result = $value;
        }
        else {
            $result = $this->getTokenFactory()->createScalarToken($value);
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
                }
                else {
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
     * @param array $tokens Array of tokens
     * @param array|null $variables Array of variables
     * @param array|null $identifiers Array of identifiers
     *
     * @return int|float
     *
     * @throws ExecException
     */
    public function calculate(array $tokens, ?array $variables = [], ?array $identifiers = [])
    {
        $stack = [];
        foreach ($tokens as $token) {
            if ($token instanceof TokenFunction) {
                $this->executeToken($token, $stack);
            }
            elseif ($token instanceof AbstractTokenOperator) {
                if (empty($stack)) {
                    throw new ExecException('Incorrect expression ', ExecException::CALC_INCORRECT_EXPRESSION);
                }
                $this->executeToken($token, $stack);
            }
            elseif ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
            }
            elseif ($token instanceof TokenScalar) {
                $stack[] = $token;
            }
            elseif ($token instanceof TokenIdentifier) {
                $identifier = $token->getValue();
                if (isset($identifiers[$identifier])) {
                    if ($identifiers[$identifier] instanceof AbstractToken) {
                        $token = $this->executeToken($token, $stack);
                    }
                    else {
                        if (is_callable($identifiers[$identifier])) {
                            $value = $this->container->get('Calculator')->execute(call_user_func($identifiers[$identifier], $identifier));
                        }
                        elseif (is_scalar($identifiers[$identifier])) {
                            $value = $identifiers[$identifier];
                        }
                        else {
                            $value = null;
                        }
                        if (is_numeric($value)) {
                            $token = $this->getTokenFactory()->createScalarToken($value);
                        }
                        else {
                            $value = $this->container->get('Calculator')->execute($value);
                            $token = $this->getTokenFactory()->createScalarToken($value);
                        }
                    }
                }
                elseif ($handler = $this->getUnknownIdentifierHandler()) {
                    $token = call_user_func($handler, $identifier);
                    if (is_scalar($token)) {
                        $token = $this->getTokenFactory()->createScalarToken($token);
                    }
                    elseif (!($token instanceof AbstractToken)) {
                        throw new ExecException('Incorrect expression ', ExecException::CALC_INCORRECT_EXPRESSION);
                    }
                }
                else {
                    throw new UnknownIdentifier('Unknown identifier "' . $identifier . '"');
                }
                $stack[] = $token;
            }
            elseif ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if ($token->assignVariable) {
                    $stack[] = $variable;
                }
                else {
                    if ($variables && array_key_exists($variable, $variables)) {
                        $value = $variables[$variable];
                    }
                    elseif ($handler = $this->getUnknownVariableHandler()) {
                        $value = call_user_func($handler, $this->container->get('Calculator'), $variable, $variables);
                    }
                    else {
                        throw new UnknownVariable('Unknown variable "' . $variable . '"');
                    }
                    if ($value instanceof AbstractToken) {
                        $stack[] = $value;
                    }
                    elseif (is_scalar($value)) {
                        $stack[] = $this->getTokenFactory()->createScalarToken($value);
                    }
                    else {
                        throw new ExecException('Incorrect expression ', ExecException::CALC_INCORRECT_EXPRESSION);
                    }
                }
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new ExecException('Incorrect expression ', ExecException::CALC_INCORRECT_EXPRESSION);
        }

        return $result->getValue();
    }

    /**
     * @param string $name
     * @param array $stack
     *
     * @return TokenScalar
     *
     * @throws Exception\LexerException
     * @throws ExecException
     */
    public function callFunction(string $name, array &$stack)
    {
        if (!isset($this->functions[$name])) {
            $this->functions[$name] = $this->getTokenFactory()->createFunction($name);
        }
        return $this->executeToken($this->functions[$name], $stack, true);
    }

    /**
     * @param string $type
     * @param callable $handler
     *
     * @return $this
     */
    public function setHandler(string $type, callable $handler)
    {
        $key = strtolower(preg_replace("/[A-Z]/",  "_$0", lcfirst($type)));
        $this->handlers[$key] = $handler;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return callable|null
     */
    public function getHandler(string $type)
    {
        $key = strtolower(preg_replace("/[A-Z]/",  "_$0", lcfirst($type)));

        return $this->handlers[$key] ?? null;
    }

    /**
     * @param callable $handler
     *
     * @return $this
     */
    public function setUnknownIdentifierHandler(callable $handler)
    {
        return $this->setHandler('UnknownIdentifier', $handler);
    }

    /**
     * @return callable|null
     */
    public function getUnknownIdentifierHandler()
    {
        return $this->getHandler('UnknownIdentifier');
    }

    /**
     * @param callable $handler
     *
     * @return $this
     */
    public function setUnknownVariableHandler(callable $handler)
    {
        return $this->setHandler('UnknownVariable', $handler);
    }

    /**
     * @return callable|null
     */
    public function getUnknownVariableHandler()
    {
        return $this->getHandler('UnknownVariable');
    }

}
