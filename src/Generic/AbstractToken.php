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

    /** @var string  */
    protected $lexeme;

    /** @var string  */
    protected $value;

    /** @var array  */
    protected $options;

    /** @var  Processor */
    protected $calculator;

    /**
     * @param string $lexeme
     * @param array  $options
     */
    public function __construct($lexeme, array $options = [])
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
            'pattern'  => (null === $pattern) ? static::$pattern : $pattern,
            'matching' => static::$matching,
            'callback' => static::$callback,
            ];
    }

    /**
     * @param string           $tokenStr
     * @param AbstractToken[] $prevTokens
     *
     * @return bool
     */
    public static function isMatch($tokenStr, $prevTokens)
    {
        return static::$pattern === $tokenStr;
    }

    /**
     * @param Processor $calculator
     */
    public function setCalculator($calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @return Processor
     */
    public function getCalculator()
    {
        return $this->calculator;
    }
}
