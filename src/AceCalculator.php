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

use avadim\AceCalculator\Exception\AceCalculatorException;
use avadim\AceCalculator\Exception\ExecException;
use avadim\AceCalculator\Exception\ConfigException;
use avadim\AceCalculator\Exception\LexerException;

use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalar;

/**
 * Class AceCalculator
 *
 * @package avadim\AceCalculator
 */
class AceCalculator
{
    const RESULT_VARIABLE           = '_';
    const VAR_PREFIX                = '$';

    const IDENTIFIER_AUTO           = 1;
    const IDENTIFIER_AS_STRING      = 1;
    const IDENTIFIER_AS_VARIABLE    = 2;
    const IDENTIFIER_AS_CALLABLE    = 3;

    const NON_NUMERIC_STRICT        = 0;
    const NON_NUMERIC_IGNORE        = 1;

    public $tokensStream = [];
    public $tokensStack = [];

    /**
     * Current config array
     *
     * @var array
     */
    private $config = [];

    /**
     * Loaded extensions
     *
     * @var array
     */
    private $extensions = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $cacheEnable = true;

    /**
     * Available variables
     *
     * @var array
     */
    private $variables = [];

    /**
     * Available callable identifiers
     *
     * @var array
     */
    private $identifiers = [];

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var callable
     */
    private $divisionByZeroHandler;

    /**
     * @var bool
     */
    private $multipleExpressions = true;

    /**
     * Base math operators
     *
     * @param array|null $config
     *
     * @throws ConfigException
     */
    public function __construct(?array $config = null)
    {
        $this->init($config);
    }

    /**
     * Clone object and renew all objects
     *
     * @throws ConfigException
     */
    public function __clone()
    {
        $this->init($this->getConfig());
    }

    /**
     * @param array|null $config
     *
     * @throws ConfigException
     */
    protected function init(?array $config = null)
    {
        $this->container = new Container();
        $this->container->set('Calculator', $this);
        $this->container->set('TokenFactory', $this->createTokenFactory($this->container));
        $this->container->set('Lexer', $this->createLexer($this->container));
        $this->container->set('Processor', $this->createProcessor($this->container));

        if (null === $config) {
             $config = $this->getDefaults();
        }
        $this->setConfig($config);
    }

    /**
     * @param bool $flag
     */
    public function cacheEnable(bool $flag)
    {
        $this->cacheEnable = (bool)$flag;
    }

    /**
     * @param Container $container
     *
     * @return TokenFactory
     */
    public function createTokenFactory(Container $container)
    {
        return new TokenFactory($container);
    }

    /**
     * @param Container $container
     *
     * @return Lexer
     */
    public function createLexer(Container $container)
    {
        return new Lexer($container);
    }

    /**
     * @param Container $container
     *
     * @return Processor
     */
    public function createProcessor(Container $container)
    {
        return new Processor($container);
    }

    /**
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return $this->container->get('TokenFactory');
    }

    /**
     * @return Lexer
     */
    public function getLexer()
    {
        return $this->container->get('Lexer');
    }

    /**
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->container->get('Processor');
    }

    /**
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'options' => [
                'var_prefix'        => self::VAR_PREFIX,
                'result_variable'   => self::RESULT_VARIABLE,
                'identifier_as'     => self::IDENTIFIER_AUTO,
                'non_numeric'       => self::NON_NUMERIC_STRICT,
            ],
            'tokens' => [
                'left_bracket'  => '\avadim\AceCalculator\Token\TokenLeftBracket',
                'right_bracket' => '\avadim\AceCalculator\Token\TokenRightBracket',
                'comma'         => '\avadim\AceCalculator\Token\TokenComma',
                'number'        => '\avadim\AceCalculator\Token\TokenScalarNumber',
                'string'        => '\avadim\AceCalculator\Token\TokenScalarString',
                'hex_number'    => '\avadim\AceCalculator\Token\TokenScalarHexNumber',
                'variable'      => ['\avadim\AceCalculator\Token\TokenVariable', self::VAR_PREFIX],
                'identifier'    => '\avadim\AceCalculator\Token\TokenIdentifier',
                'function'      => '\avadim\AceCalculator\Token\TokenFunction',
            ],
            'operators' => [
                'plus'          => '\avadim\AceCalculator\Token\Operator\TokenOperatorPlus',
                'minus'         => '\avadim\AceCalculator\Token\Operator\TokenOperatorMinus',
                'multiply'      => '\avadim\AceCalculator\Token\Operator\TokenOperatorMultiply',
                'division'      => '\avadim\AceCalculator\Token\Operator\TokenOperatorDivide',
                'power'         => '\avadim\AceCalculator\Token\Operator\TokenOperatorPower',
                'assign'        => '\avadim\AceCalculator\Token\Operator\TokenOperatorAssign',
            ],
            'functions' => [
                // name => [callback, minArguments, variableArguments]
                'abs'   => 'abs',
                'min'   => ['min', 2, true],
                'max'   => ['max', 2, true],
                'avg'   => [static function() { return array_sum(func_get_args()) / func_num_args(); }, 2, true],
                'sqrt'  => 'sqrt',
                'log'   => 'log',
                'log10' => 'log10',
                'exp'   => 'exp',
                'floor' => 'floor',
                'ceil'  => 'ceil',
                'round' => ['round', 1, true],
                'sin'   => 'sin',
                'cos'   => 'cos',
                'tn'    => 'tan',
                'tan'   => 'tan',
                'asin'  => 'asin',
                'acos'  => 'acos',
                'atn'   => 'atan',
                'degrees' => 'rad2deg',
                'radians' => 'deg2rad',
                'rad2deg' => 'rad2deg',
                'deg2rad' => 'deg2rad',
            ],
            'variables' => [
                'pi' => 3.14159265359,
                'e'  => 2.71828182846
            ],
            'identifiers' => [
                'PI' => M_PI,
                'E'  => M_E
            ],
        ];
    }

    /**
     * Apply operands and functions
     *
     * @param array $config
     *
     * @throws ConfigException
     */
    protected function applyConfig(array $config)
    {
        if (isset($config['options'])) {
            $this->config['options'] = $config['options'];
        }

        $tokenFactory = $this->getTokenFactory();

        // set default tokens
        if (isset($config['tokens'])) {
            foreach((array)$config['tokens'] as $name => $options) {
                if (is_array($options)) {
                    list($class, $pattern) = $options;
                } else {
                    $class = $options;
                    $pattern = null;
                }
                $tokenFactory->addToken($name, $class, $pattern);
            }
        }

        // set default operators
        if (isset($config['operators'])) {
            foreach((array)$config['operators'] as $name => $class) {
                $tokenFactory->addOperator($name, $class);
            }
        }

        // set default functions
        if (isset($config['functions'])) {
            foreach((array)$config['functions'] as $name => $options) {
                $minArguments = null;
                $variableArguments = null;
                if (is_array($options)) {
                    switch (count($options)) {
                        case 1:
                            $callback = reset($options);
                            break;
                        case 2:
                            list($callback, $minArguments) = $options;
                            break;
                        default:
                            list($callback, $minArguments, $variableArguments) = $options;
                    }
                } else {
                    $callback = $options;
                }
                $function = static::createFunction($name, $callback, $minArguments, $variableArguments);
                $tokenFactory->addFunction($name, $function);
            }
        }

        // set default variables
        if (isset($config['variables'])) {
            $this->setVars($config['variables']);
        }
        if (isset($config['identifiers'])) {
            $this->setIdentifiers($config['identifiers']);
        }
        if (isset($config['options']['result_variable'])) {
            $this->setVar($config['options']['result_variable'], null);
        }
    }

    /**
     * @param array $config
     *
     * @return $this
     *
     * @throws ConfigException
     */
    protected function setConfig(array $config)
    {
        $this->applyConfig($config);
        $this->config = $config;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return $this
     *
     * @throws ConfigException
     */
    protected function addConfig(array $config)
    {
        $this->applyConfig($config);
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @param string $configFile
     *
     * @return $this
     *
     * @throws ConfigException
     */
    public function loadConfig(string $configFile)
    {
        if (is_file($configFile)) {
            $config = include($configFile);
            if (is_array($config)) {
                if (isset($config['extensions'])) {
                    foreach((array)$config['extensions'] as $extension) {
                        $this->loadExtension($extension);
                    }
                }
                if (isset($config['include'])) {
                    $dir = dirname($configFile) . '/';
                    $includes = (array)$config['include'];
                    foreach($includes as $filePattern) {
                        if ($filePattern && $filePattern[0] !== '.' && false === strpos($filePattern, '/.')) {
                            $files = glob($dir . $filePattern);
                            foreach($files as $includeFile) {
                                if ($includeFile !== $configFile) {
                                    include_once $includeFile;
                                }
                            }
                        }
                    }
                }
                $this->addConfig($config);
            } else {
                throw new ConfigException('Config is not array');
            }
        } else {
            throw new ConfigException('Config file does not exist');
        }

        return $this;
    }

    /**
     * @param string $extensionName
     * @param string|null $path
     *
     * @return AceCalculator
     *
     * @throws ConfigException
     */
    public function loadExtension(string $extensionName, string $path = null)
    {
        if (!isset($this->extensions[$extensionName])) {
            if (null === $path) {
                $path = __DIR__ . '/Extension/' . $extensionName . '/config.php';
            }
            $this->loadConfig($path);
            $this->extensions[$extensionName] = $path;
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setConfigOption(string $name, $value)
    {
        $this->config['options'][$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getConfigOption(string $name)
    {
        if (isset($this->config['options'][$name])) {
            return $this->config['options'][$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption(string $name, $value)
    {
        $this->setConfigOption($name, $value);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name)
    {
        return $this->getConfigOption($name);
    }

    /**
     * Add variable to executor
     *
     * @param string $variable
     * @param integer|float $value
     *
     * @return AceCalculator
     */
    public function setVar(string $variable, $value)
    {
        if (($sVarPrefix = $this->getConfigOption('var_prefix')) && $variable[0] !== $sVarPrefix) {
            $variable = $sVarPrefix . $variable;
        }
        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Add variables to executor
     *
     * @param array $variables
     * @param bool|null $clear Clear previous variables
     *
     * @return AceCalculator
     */
    public function setVars(array $variables, ?bool $clear = true)
    {
        if ($clear) {
            $this->removeVars();
        }

        foreach ($variables as $name => $value) {
            $this->setVar($name, $value);
        }

        return $this;
    }

    /**
     * Remove variable from executor
     *
     * @param string $variable
     *
     * @return AceCalculator
     */
    public function removeVar(string $variable)
    {
        unset ($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables
     */
    public function removeVars()
    {
        $this->variables = [];

        return $this;
    }

    /**
     * @param string $variable
     *
     * @return mixed
     */
    public function getVar(string $variable)
    {
        if ($sVarPrefix = $this->getConfigOption('var_prefix')) {
            if ($variable[0] !== $sVarPrefix) {
                $variable = $sVarPrefix . $variable;
            }
        }
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->variables;
    }

    /**
     * Add identifier to executor
     *
     * @param string $identifier
     * @param callable|scalar|TokenScalar $value
     *
     * @return AceCalculator
     */
    public function setIdentifier(string $identifier, $value)
    {
        if (!is_scalar($value) && !is_callable($value) && !($value instanceof TokenScalar)) {
            AceCalculatorException::call(AceCalculatorException::CALC_INCORRECT_IDENTIFIER_EXPR, [$identifier]);
        }
        $this->identifiers[$identifier] = $value;

        return $this;
    }

    /**
     * Add identifiers to executor
     *
     * @param array $identifiers
     * @param bool|null $clear Clear previous identifiers
     *
     * @return AceCalculator
     */
    public function setIdentifiers(array $identifiers, ?bool $clear = true)
    {
        if ($clear) {
            $this->removeIdentifiers();
        }

        foreach ($identifiers as $name => $value) {
            $this->setIdentifier($name, $value);
        }

        return $this;
    }

    /**
     * Remove identifier from executor
     *
     * @param string $identifier
     *
     * @return AceCalculator
     */
    public function removeIdentifier(string $identifier)
    {
        unset ($this->identifiers[$identifier]);

        return $this;
    }

    /**
     * Remove all identifiers
     */
    public function removeIdentifiers()
    {
        $this->identifiers = [];

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getIdentifier(string $identifier)
    {
        if (isset($this->identifiers[$identifier])) {
            return $this->identifiers[$identifier];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getIdentifiers()
    {

        return $this->identifiers;
    }

    /**
     * Add operator to executor
     *
     * @param string|object $name
     * @param string|array|null $operatorClass Class of operator token or options for new operator
     *
     * @return AceCalculator
     *
     * @throws ConfigException
     */
    public function addOperator($name, $operatorClass = null)
    {
        if ($name instanceof AbstractTokenOperator) {
            $operatorClass = $name;
            $name = $operatorClass->getPattern();
        }
        $this->getTokenFactory()->addOperator($name, $operatorClass);

        return $this;
    }

    /**
     * Add function to executor
     *
     * @param string $name Name of function
     * @param callable|null $callback Function
     * @param int|null $minArguments Count of arguments
     * @param bool|null $variableArguments
     *
     * @return AceCalculator
     */
    public function addFunction(string $name, ?callable $callback = null, ?int $minArguments = 1, ?bool $variableArguments = false)
    {
        $function = static::createFunction($name, $callback, $minArguments, $variableArguments);
        $this->getTokenFactory()->addFunction($name, $function);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFunctionList()
    {
        $list = $this->getTokenFactory()->getFunctionList();
        if ($list) {
            $result = array_keys($list);
            sort($result);
            return $result;
        }
        return [];
    }

    /**
     * @param callable $handler
     *
     * @return $this
     */
    public function setDivisionByZeroHandler(callable $handler)
    {
        $this->divisionByZeroHandler = $handler;

        return $this;
    }

    /**
     * @return callable
     */
    public function getDivisionByZeroHandler()
    {
        return $this->divisionByZeroHandler;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setMultipleExpressionsEnable(bool $flag)
    {
        $this->multipleExpressions = $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMultipleExpressionsEnable()
    {
        return $this->multipleExpressions;
    }

    /**
     * Execute expression
     *
     * @param string $expression
     * @param string|null $resultVariable
     *
     * @return $this
     *
     * @throws LexerException
     * @throws ExecException
     */
    public function calc(string $expression, string $resultVariable = null)
    {
        if ($expression === '') {
            throw new ExecException('Expression is empty', ExecException::CALC_INCORRECT_EXPRESSION);
        }
        if ($this->multipleExpressions) {
            $expressions = explode(';', $expression);
        }
        else {
            $expressions = [$expression];
        }
        $result = null;
        foreach($expressions as $exp) {
            $result = $this->calcExpression($exp);
        }
        $totalResultVar = $this->getConfigOption('result_variable');
        if ($resultVariable) {
            $this->setVar($resultVariable, $result);
        }
        $this->setVar($totalResultVar ?: self::RESULT_VARIABLE, $result);

        return $this;
    }

    /**
     * Execute expression
     *
     * @param string $expression
     *
     * @return mixed
     *
     * @throws LexerException
     * @throws ExecException
     */
    protected function calcExpression(string $expression)
    {
        if ($expression === '') {
            throw new ExecException('Expression is empty', ExecException::CALC_INCORRECT_EXPRESSION);
        }
        if (preg_match('/^[+-]?\d+$/', $expression)) {
            $result = (int)$expression;
        }
        elseif (is_numeric($expression)) {
            $result = (float)$expression;
        }
        else {
            if (!$this->cacheEnable || !isset($this->cache[$expression])) {
                $lexer = $this->getLexer();
                $this->tokensStream = $lexer->stringToTokensStream($expression);
                $this->tokensStack = $lexer->buildReversePolishNotation($this->tokensStream);
                if ($this->cacheEnable) {
                    $this->cache[$expression] = $this->tokensStack;
                }
            } else {
                $this->tokensStack = $this->cache[$expression];
            }
            $processor = $this->getProcessor();
            try {
                $result = $processor->calculate($this->tokensStack, $this->variables, $this->identifiers);
            } catch (ExecException $e) {
                $exception = new ExecException('Expression calculation error: ' . $e->getMessage() . '. Expression: ' . $expression);
                $exception->setErrorMessage($e->getMessage());
                $exception->setErrorExpression($expression);
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function result()
    {
        $resultVariable = $this->getConfigOption('result_variable');
        if ($resultVariable) {
            return $this->getVar(self::RESULT_VARIABLE);
        }
        return null;
    }

    /**
     * Execute expression
     *
     * @param string $expression
     * @param string|null $resultVariable
     *
     * @return number
     *
     * @throws ExecException
     * @throws LexerException
     */
    public function execute(string $expression, string $resultVariable = null)
    {
        $this->calc($expression, $resultVariable);

        return $this->result();
    }

    /**
     * @param callable $handler
     *
     * @return $this
     */
    public function setUnknownIdentifierHandler(callable $handler)
    {
        $this->getProcessor()->setUnknownIdentifierHandler($handler);

        return $this;
    }

    /**
     * @param callable $handler
     *
     * @return $this
     */
    public function setUnknownVariableHandler(callable $handler)
    {
        $this->getProcessor()->setUnknownVariableHandler($handler);

        return $this;
    }

    /**
     * Add function
     *
     * @param string $name
     * @param callable $callback
     * @param int|null $minArguments
     * @param bool|null $variableArguments
     *
     * @return array;
     */
    public static function createFunction(string $name, callable $callback, ?int $minArguments = 1, ?bool $variableArguments = false)
    {
        if (null === $minArguments) {
            $minArguments = 1;
        } elseif ($minArguments === -1) {
            $minArguments = 0;
            $variableArguments = true;
        }
        return [$name, $minArguments, $callback, $variableArguments];
    }

}
