<?php

namespace avadim\AceCalculator\Token\Operator;

use avadim\AceCalculator\Generic\AbstractTokenOperator;

class TokenOperator extends AbstractTokenOperator
{
    private $priority;
    private $association;
    private $execute;

    /**
     * @param string $lexeme
     * @param int|array $options
     * @param callback $func
     */
    public function __construct(string $lexeme, $options, callable $func)
    {
        if (is_array($options)) {
            $this->priority = $options['priority'] ?? 0;
            $this->association = $options['priority'] ?? self::LEFT_ASSOC;
        }
        else {
            $this->priority = (int)$options;
            $this->association = self::LEFT_ASSOC;
        }
        $this->execute = $func;
        parent::__construct($lexeme);
    }

    /**
     * @return string|null
     */
    public function getPattern()
    {
        return $this->lexeme;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * @param array $stack
     *
     * @return mixed
     */
    public function execute(array &$stack)
    {
        $func = $this->execute;

        return $func($stack);
    }

}