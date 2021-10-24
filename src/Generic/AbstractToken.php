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

namespace avadim\AceCalculator\Generic;

use avadim\AceCalculator\Container;
use avadim\AceCalculator\Processor;

/**
 * Class AbstractToken
 *
 * @package avadim\AceCalculator
 */
abstract class AbstractToken
{
    const MATCH_STRING   = 0;
    const MATCH_NUMERIC  = 1;
    const MATCH_REGEX    = 2;
    const MATCH_CALLBACK = 3;

    /** @var null|string  */
    protected static $pattern;

    /** @var int  */
    protected static $matching = self::MATCH_STRING;

    /** @var int  */
    protected static $callback = 'isMatch';

    /** @var int  */
    protected static $lexemes_max = 1;

    /** @var string  */
    protected $lexeme;

    /** @var string  */
    protected $value;

    /** @var array  */
    protected $options;

    /** @var  Container */
    protected $container;

    /** @var  Processor */
    protected $processor;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct(string $lexeme, array $options = [])
    {
        $this->lexeme = $lexeme;
        $this->value = $lexeme;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getLexeme()
    {
        return $this->lexeme;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueNum()
    {
        if (!is_numeric($this->value)) {
            if (empty($this->options['non_numeric'])) {
                $caller = debug_backtrace(null, 2);
                $message = 'A non-numeric value ';
                if (null === $this->value) {
                    $message .= 'NULL';
                } elseif (is_bool($this->value)) {
                    $message .= $this->value ? 'TRUE' : 'FALSE';
                } else {
                    $message .= "'" . $this->value . "'";
                }
                if (isset($caller[1]['class'])) {
                    $message .= ' for ' . $caller[1]['class'];
                }
                $message .= ' encountered';
                trigger_error($message . ')', E_USER_WARNING);
            } else {
                return ((int)$this->value == $this->value) ? (int)$this->value : (float)$this->value;
            }
        }
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    public static function getMatching($pattern = null)
    {
        return [
            'pattern'       => (null === $pattern) ? static::$pattern : $pattern,
            'matching'      => static::$matching,
            'callback'      => static::$callback,
            'lexemes_max'   => static::$lexemes_max,
        ];
    }

    /**
     * @param string          $tokenStr
     * @param AbstractToken[] $prevTokens
     * @param array           $allLexemes
     * @param int             $lexemeNum
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens, $allLexemes, &$lexemeNum)
    {
        return static::$pattern === $tokenStr;
    }

    /**
     * @param Processor $processor
     */
    public function setProcessor($processor)
    {
        $this->container->set('Processor', $processor);

        return $this;
    }

    /**
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->container->get('Processor');
    }

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

}
