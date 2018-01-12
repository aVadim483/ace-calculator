<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 * Based on NeonXP/MathExecutor by Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator;

use avadim\AceCalculator\Exception\CalcException;
use avadim\AceCalculator\Exception\ConfigException;
use avadim\AceCalculator\Exception\LexerException;
use avadim\AceCalculator\Generic\AbstractTokenScalar;

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

    /**
     * Current config array
     *
     * @var array
     */
    private $config = [];

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
     * Base math operators
     *
     * @param array $config
     *
     * @throws ConfigException
     */
    public function __construct($config = null)
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
     * @param array $config
     *
     * @throws ConfigException
     */
    protected function init($config = null)
    {
        $this->container = new Container();
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
    public function cacheEnable($flag)
    {
        $this->cacheEnable = (bool)$flag;
    }

    /**
     * @param Container $container
     *
     * @return TokenFactory
     */
    public function createTokenFactory($container)
    {
        return new TokenFactory($container);
    }

    /**
     * @param Container $container
     *
     * @return Lexer
     */
    public function createLexer($container)
    {
        return new Lexer($container);
    }

    /**
     * @param Container $container
     *
     * @return Processor
     */
    public function createProcessor($container)
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
    public function getCalculator()
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
            ],
            'tokens' => [
                'left_bracket'  => '\avadim\AceCalculator\Token\TokenLeftBracket',
                'right_bracket' => '\avadim\AceCalculator\Token\TokenRightBracket',
                'comma'         => '\avadim\AceCalculator\Token\TokenComma',
                'number'        => '\avadim\AceCalculator\Token\TokenScalarNumber',
                'string'        => '\avadim\AceCalculator\Token\TokenScalarString',
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
            ],
            'functions' => [
                'min'   => ['min', 2, true],
                'max'   => ['max', 2, true],
                'avg'   => [function() { return array_sum(func_get_args()) / func_num_args(); }, 2, true],
                'sqrt'  => 'sqrt',
                'sin'   => 'sin',
                'cos'   => 'cos',
                'tn'    => 'tan',
                'asin'  => 'asin',
                'acos'  => 'acos',
                'atn'   => 'atan',
            ],
            'variables' => [
                'pi' => 3.14159265359,
                'e'  => 2.71828182846
            ],
        ];
    }

    /**
     * Apply operands and functions
     *
     * @param  array $config
     *
     * @throws ConfigException
     */
    protected function applyConfig($config)
    {
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
        if (isset($config['options']['result_variable'])) {
            $this->setVar($config['options']['result_variable'], null);
        }
    }

    /**
     * @param $config
     *
     * @return $this
     *
     * @throws ConfigException
     */
    protected function setConfig($config)
    {
        $this->applyConfig($config);
        $this->config = $config;

        return $this;
    }

    /**
     * @param $config
     *
     * @return $this
     *
     * @throws ConfigException
     */
    protected function addConfig($config)
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
    public function loadConfig($configFile)
    {
        if (is_file($configFile)) {
            $config = include($configFile);
            if (is_array($config)) {
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
     * @param string $path
     *
     * @return AceCalculator
     *
     * @throws ConfigException
     */
    public function loadExtension($extensionName, $path = null)
    {
        if (null === $path) {
            $path = __DIR__ . '/Extension/' . $extensionName . '/config.php';
        }
        return $this->loadConfig($path);
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
     *
     * @return mixed
     */
    protected function getConfigOption($name)
    {
        if (isset($this->config['options'][$name])) {
            return $this->config['options'][$name];
        }
        return null;
    }

    /**
     * Add variable to executor
     *
     * @param  string        $variable
     * @param  integer|float $value
     *
     * @return AceCalculator
     */
    public function setVar($variable, $value)
    {
        if ($sVarPrefix = $this->getConfigOption('var_prefix')) {
            if ($variable[0] !== $sVarPrefix) {
                $variable = $sVarPrefix . $variable;
            }
        }
        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Add variables to executor
     *
     * @param  array        $variables
     * @param  bool         $clear     Clear previous variables
     *
     * @return AceCalculator
     */
    public function setVars(array $variables, $clear = true)
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
     * @param  string       $variable
     *
     * @return AceCalculator
     */
    public function removeVar($variable)
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
    public function getVar($variable)
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
     * Add identifier to executor
     *
     * @param string $identifier
     * @param callable|AbstractTokenScalar $value
     *
     * @return AceCalculator
     */
    public function setIdentifier($identifier, $value)
    {
        $this->identifiers[$identifier] = $value;

        return $this;
    }

    /**
     * Add identifiers to executor
     *
     * @param array $identifiers
     * @param bool  $clear Clear previous identifiers
     *
     * @return AceCalculator
     */
    public function setIdentifiers(array $identifiers, $clear = true)
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
    public function removeIdentifier($identifier)
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
    public function getIdentifier($identifier)
    {
        if (isset($this->variables[$identifier])) {
            return $this->variables[$identifier];
        }
        return null;
    }

    /**
     * Add operator to executor
     *
     * @param  string   $name
     * @param  string   $operatorClass Class of operator token
     *
     * @return AceCalculator
     *
     * @throws ConfigException
     */
    public function addOperator($name, $operatorClass)
    {
        $this->getTokenFactory()->addOperator($name, $operatorClass);

        return $this;
    }

    /**
     * Add function to executor
     *
     * @param string       $name     Name of function
     * @param callable     $callback Function
     * @param int          $minArguments   Count of arguments
     * @param bool         $variableArguments
     *
     * @return AceCalculator
     */
    public function addFunction($name, callable $callback = null, $minArguments = 1, $variableArguments = false)
    {
        $function = static::createFunction($name, $callback, $minArguments, $variableArguments);
        $this->getTokenFactory()->addFunction($name, $function);

        return $this;
    }

    /**
     * Execute expression
     *
     * @param string $expression
     * @param string $resultVariable
     *
     * @return $this
     *
     * @throws LexerException
     * @throws CalcException
     */
    public function calc($expression, $resultVariable = null)
    {
        if (!$this->cacheEnable || !isset($this->cache[$expression])) {
            $lexer = $this->getLexer();
            $tokensStream = $lexer->stringToTokensStream($expression);
            $tokensStack = $lexer->buildReversePolishNotation($tokensStream);
            if ($this->cacheEnable) {
                $this->cache[$expression] = $tokensStack;
            }
        } else {
            $tokensStack = $this->cache[$expression];
        }
        $calculator = $this->getCalculator();
        $result = $calculator->calculate($tokensStack, $this->variables, $this->identifiers);

        if (!$resultVariable) {
            $resultVariable = $this->getConfigOption('result_variable');
        }
        if ($resultVariable) {
            $this->setVar($resultVariable ?: self::RESULT_VARIABLE, $result);
        }
        return $this;
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
     * @param $expression
     *
     * @return number
     *
     * @throws CalcException
     * @throws LexerException
     */
    public function execute($expression)
    {
        $this->calc($expression);

        return $this->result();
    }

    /**
     * Add function
     *
     * @param string   $name
     * @param callable $callback
     * @param int      $minArguments
     * @param bool     $variableArguments
     *
     * @return mixed;
     */
    public static function createFunction($name, $callback, $minArguments = 1, $variableArguments = false)
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
