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

use avadim\AceCalculator\Token\Operator\TokenOperatorAssign;
use avadim\AceCalculator\Token\TokenComma;
use avadim\AceCalculator\Token\TokenFunction;
use avadim\AceCalculator\Token\TokenIdentifier;
use avadim\AceCalculator\Token\TokenLeftBracket;
use avadim\AceCalculator\Token\TokenRightBracket;
use avadim\AceCalculator\Token\TokenVariable;
use avadim\AceCalculator\Token\TokenScalar;

use avadim\AceCalculator\Exception\LexerException;

/**
 * Class Lexer
 *
 * @package avadim\AceCalculator
 */
class Lexer
{
    /** @var Container */
    private $container;

    /** @var array  */
    private $lexemes = [];

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
     * @return TokenFactory
     */
    public function getTokenFactory()
    {
        return $this->container->get('TokenFactory');
    }

    public function setLexemes($lexemes)
    {
        $this->lexemes = $lexemes;

        return $this;
    }

    public function getLexemes()
    {
        return $this->lexemes;
    }

    /**
     * @param string $input Source string of equation
     *
     * @return $this
     */
    public function init($input)
    {
        $lexemes = $this->parse($input);
        $this->setLexemes($lexemes);

        return $this;
    }

    /**
     * @param $input
     *
     * @return array
     */
    protected function parse($input)
    {
        // parse to lexemes array
        $phpTokens = token_get_all('<?php ' . $input);
        array_shift($phpTokens);

        $lexemesArray = [];
        foreach($phpTokens as $phpToken) {
            if (is_string($phpToken)) {
                $lexemeStr = $phpToken;
            } elseif(isset($phpToken[0], $phpToken[1])) {
                $lexemeStr = ($phpToken[0] === T_WHITESPACE) ? ' ' : $phpToken[1];
            } else {
                $lexemeStr = null;
            }
            if (null !== $lexemeStr) {
                if (strlen($lexemeStr) > 1 && ($lexemeStr[0] === '#' || $lexemeStr[0] === '/')) {
                    $lexemesArray[] = [$lexemeStr[0]];
                    $lexemesArray[] = $this->parse(substr($lexemeStr, 1));
                } else {
                    $lexemesArray[] = [$lexemeStr];
                }
            }
        }
        return array_merge(...$lexemesArray);
    }

    /**
     * @return array
     *
     * @throws LexerException
     */
    public function getTokensStream()
    {
        // convert lexemes to tokens
        $tokensStream = [];
        $lexemes = $this->getLexemes();
        $tokenFactory = $this->getTokenFactory();
        $lexemeNum = 0;
        $lexemeCnt = count($lexemes);
        while ($lexemeNum < $lexemeCnt) {
            if ($lexemes[$lexemeNum] !== ' ') {
                $tokensStream[] = $tokenFactory->createToken($lexemes, $lexemeNum, $tokensStream);
            }
            ++$lexemeNum;
        }
        /*
        foreach ($lexemes as $lexemeNum => $lexemeStr) {
            $tokensStream[] = $tokenFactory->createToken($lexemeStr, $tokensStream, $lexemes, $lexemeNum);
        }
        */
        foreach ($tokensStream as $num => $token) {
            // convert identifiers to functions
            if ($token instanceof TokenIdentifier && isset($tokensStream[$num + 1]) && $tokensStream[$num + 1] instanceof TokenLeftBracket) {
                $tokensStream[$num] = $tokenFactory->createFunction($token->getLexeme());
            }
            // check assign operator
            if ($token instanceof TokenOperatorAssign && isset($tokensStream[$num - 1]) && $tokensStream[$num - 1] instanceof TokenVariable) {
                //$tokensStream[$num] = $tokenFactory->createFunction($token->getLexeme());
            }
        }
        return $tokensStream;
    }

    /**
     * Parse input string and returns tokens stream
     *
     * @param  string $input Source string of equation
     *
     * @return array
     *
     * @throws LexerException
     */
    public function stringToTokensStream($input)
    {
        $this->init($input);

        return $this->getTokensStream();
    }

    /**
     * Returns tokens in revers polish notation
     *
     * @param  array $tokensStream Tokens stream
     *
     * @return array
     *
     * @throws LexerException
     */
    public function buildReversePolishNotation($tokensStream)
    {
        $output = [];
        $stack = [];
        $function = 0;
        $level = 0;

        foreach ($tokensStream as $token) {
            if ($token instanceof TokenFunction) {
                $stack[] = $token;
                ++$function;
            } elseif ($token instanceof TokenScalar || $token instanceof TokenVariable || $token instanceof TokenIdentifier) {
                $output[] = $token;
            } elseif ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
                if ($function > $level) {
                    $output[] = $token;
                    ++$level;
                }
            } elseif ($token instanceof TokenComma) {
                while ($stack && (!$stack[count($stack)-1] instanceof TokenLeftBracket)) {
                    $output[] = array_pop($stack);
                    if (empty($stack)) {
                        throw new LexerException('Incorrect expression', LexerException::LEXER_ERROR);
                    }
                }
            } elseif ($token instanceof TokenRightBracket) {
                --$level;
                while (($current = array_pop($stack)) && (!$current instanceof TokenLeftBracket)) {
                    $output[] = $current;
                }
                if (!empty($stack) && ($stack[count($stack)-1] instanceof TokenFunction)) {
                    $output[] = array_pop($stack);
                }
                if ($function > $level) {
                    --$function;
                }
            } elseif ($token instanceof TokenOperatorAssign) {
                if (count($output) > 0 && ($prevToken = array_pop($output)) && ($prevToken instanceof TokenVariable) && $prevToken->getOption('begin')) {
                    // assign to variable
                    $prevToken->assignVariable = true;
                    $stack[] = $token;
                    $stack[] = $prevToken;
                } else {
                    throw new LexerException('Variable expected before "="', LexerException::LEXER_ERROR);
                }
            } elseif ($token instanceof AbstractTokenOperator) {
                while (($count = count($stack)) > 0 && $token->lowPriority($stack[$count-1])) {
                    $output[] = array_pop($stack);
                }

                $stack[] = $token;
            }
        }
        while (!empty($stack)) {
            $token = array_pop($stack);
            if ($token instanceof TokenLeftBracket || $token instanceof TokenRightBracket) {
                throw new LexerException('Incorrect brackets expression', LexerException::LEXER_ERROR);
            }
            $output[] = $token;
        }

        return $output;
    }

}
