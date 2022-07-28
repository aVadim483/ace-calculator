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

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenDelimiter;
use avadim\AceCalculator\Generic\AbstractTokenGroup;
use avadim\AceCalculator\Generic\AbstractTokenOperator;

use avadim\AceCalculator\Exception\ConfigException;
use avadim\AceCalculator\Exception\LexerException;

use avadim\AceCalculator\Token\TokenFunction;
use avadim\AceCalculator\Token\TokenScalar;
use avadim\AceCalculator\Token\TokenScalarNumber;
use avadim\AceCalculator\Token\TokenScalarString;
use avadim\AceCalculator\Token\TokenVariable;

/**
 * Class TokenFactory
 *
 * @package avadim\AceCalculator
 */
class TokenFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Available tokens (not functions)
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Available functions
     *
     * @var array
     */
    protected $functions = [];

    /**
     * Lexer constructor.
     *
     * @param Container $container
     */
    public function __construct($container = null)
    {
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
     * @param string $name
     * @param string|object $tokenClass
     * @param string|null $pattern
     * @param bool|null $prepend
     *
     * @throws ConfigException
     */
    protected function registerToken(string $name, $tokenClass, string $pattern = null, ?bool $prepend = false)
    {
        $matching = $tokenClass::getMatching($pattern);
        if (!isset($matching['pattern']) && !isset($matching['matching'])) {
            throw new ConfigException('Method class "' . $tokenClass . '::getMatching()" returns bad array', ConfigException::CONFIG_OPERATOR_BAD_INTERFACE);
        }
        $matching['class'] = $tokenClass;

        if ($prepend && !isset($this->tokens[$name])) {
            $this->tokens = array_merge([$name => $matching], $this->tokens);
        } else {
            $this->tokens[$name] = $matching;
        }
    }

    /**
     * Add token class
     *
     * @param string $name
     * @param string $class
     * @param string|null $pattern
     *
     * @throws ConfigException
     */
    public function addToken(string $name, string $class, string $pattern = null)
    {
        $this->registerToken($name, $class, $pattern, false);
    }

    /**
     * Add operator class
     *
     * @param string $name
     * @param string|object $class
     *
     * @throws ConfigException
     */
    public function addOperator(string $name, $class)
    {
        if (is_object($class)) {
            $this->registerToken($name, $class, $class->getPattern(), true);
        }
        else {
            $this->registerToken($name, $class, null, true);
        }
    }

    /**
     * Add function
     *
     * @param string $name
     * @param mixed $function
     */
    public function addFunction(string $name, $function)
    {
        $this->functions[$name] = $function;
    }

    /**
     * @return array
     */
    public function getFunctionList(): array
    {
        return $this->functions;
    }

    /**
     * @param string|object $tokenClass
     * @param mixed $value
     * @param array|null $options
     *
     * @return AbstractToken
     */
    protected function createTokenByClass($tokenClass, $value, ?array $options = [])
    {
        $calculator = $this->container->get('Calculator');
        $options['non_numeric'] = !empty($calculator) ? $calculator->getOption('non_numeric') : null;

        if ($value instanceof AbstractToken) {
            $value = $value->getValue();
        }
        if (is_string($tokenClass)) {
            /** @var AbstractToken $token */
            $token = new $tokenClass($value, $options);
        }
        else {
            $token = $tokenClass;
            $token->setValue($value);
        }
        $token->setContainer($this->container);

        return $token;
    }

    /**
     * Create token object
     *
     * @param array $allLexemes    Array of all lexemes
     * @param int $lexemeNum     Number of current lexeme in array
     * @param array $tokensStream  Stream of previous tokens
     *
     * @return AbstractToken
     *
     * @throws LexerException
     */
    public function createToken(array $allLexemes, int &$lexemeNum, array $tokensStream)
    {
        $lexeme = $allLexemes[$lexemeNum];
        if ($tokensStream) {
            $prevToken = end($tokensStream);
            $beginExpression = ($prevToken instanceof AbstractTokenOperator || $prevToken instanceof AbstractTokenGroup || $prevToken instanceof AbstractTokenDelimiter);
        } else {
            $prevToken = null;
            $beginExpression = true;
        }

        $options = [
            'begin' => $beginExpression,
        ];

        if (is_numeric($lexeme)) {
            $tokenClass = $this->tokens['number']['class'];

            return $this->createTokenByClass($tokenClass, $lexeme, $options);
        }

        foreach ($this->tokens as $tokenName => $tokenMatching) {
            $tokenClass = $tokenMatching['class'];
            $tokenCallback = $tokenMatching['callback'];
            $lexemesMax = !empty($tokenMatching['lexemes_max']) ? $tokenMatching['lexemes_max'] : 1;

            switch ($tokenMatching['matching']) {
                case AbstractToken::MATCH_CALLBACK:
                    if ($lexemesMax > 1) {
                        // Concatenate several lexemes
                        $checkLexeme = '';
                        for($i = 0; $i < $lexemesMax; $i++) {
                            $checkLexeme .= $allLexemes[$lexemeNum + $i];
                            if ($tokenClass::$tokenCallback($checkLexeme, $tokensStream, $allLexemes, $lexemeNum)) {
                                $lexemeNum += $i;
                                return $this->createTokenByClass($tokenClass, $checkLexeme, $options);
                            }
                        }
                    } elseif ($callResult = $tokenClass::$tokenCallback($lexeme, $tokensStream, $allLexemes, $lexemeNum)) {
                        if (is_string($callResult)) {
                            $lexeme = $callResult;
                        }
                        return $this->createTokenByClass($tokenClass, $lexeme, $options);
                    }
                    break;
                case AbstractToken::MATCH_REGEX:
                    if ($lexemesMax > 1) {
                        // Concatenate several lexemes
                        $checkLexeme = '';
                        for($i = 0; $i < $lexemesMax; $i++) {
                            $checkLexeme .= $allLexemes[$lexemeNum + $i];
                            if (preg_match($tokenMatching['pattern'], $checkLexeme)) {
                                $lexemeNum += $i;
                                return $this->createTokenByClass($tokenClass, $checkLexeme, $options);
                            }
                        }
                    }
                    elseif (preg_match($tokenMatching['pattern'], $lexeme)) {
                        return $this->createTokenByClass($tokenClass, $lexeme, $options);
                    }
                    break;
                case AbstractToken::MATCH_NUMERIC:
                    if (is_numeric($lexeme)) {
                        return $this->createTokenByClass($tokenClass, $lexeme, $options);
                    }
                    break;
                case AbstractToken::MATCH_STRING:
                default:
                    if ($tokenMatching['pattern'] === $lexeme) {
                        return $this->createTokenByClass($tokenClass, $lexeme, $options);
                    }
            }
        }
        throw new LexerException('Unknown token "' . $lexeme . '"', LexerException::LEXER_UNKNOWN_TOKEN);
    }

    /**
     * @param mixed $value
     *
     * @return AbstractToken|TokenScalar|TokenScalarNumber|TokenScalarString
     */
    public function createScalarToken($value)
    {
        if (null === $value) {
            $tokenClass = TokenScalar::class;
        }
        elseif (is_numeric($value)) {
            $tokenClass = TokenScalarNumber::class;
        }
        else {
            $tokenClass = TokenScalarString::class;
        }
        return $this->createTokenByClass($tokenClass, $value);
    }

    /**
     * Create function object
     *
     * @param string $name
     *
     * @return AbstractToken|TokenFunction
     *
     * @throws LexerException
     */
    public function createFunction(string $name)
    {
        if (isset($this->functions[$name], $this->tokens['function']['class'])) {
            $tokenClass = $this->tokens['function']['class'];
            $tokenOptions = $this->functions[$name] ?? [];
            return $this->createTokenByClass($tokenClass, $name, $tokenOptions);
        }
        throw new LexerException('Unknown function "' . $name . '"', LexerException::LEXER_UNKNOWN_FUNCTION);
    }

    /**
     * Returns registered functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }
}
