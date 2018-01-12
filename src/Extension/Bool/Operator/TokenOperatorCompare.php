<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\Bool\Operator;

use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Generic\AbstractTokenScalar;
use avadim\AceCalculator\Token\TokenScalarNumber;

/**
 * Class TokenOperatorCompare
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorCompare extends AbstractTokenOperator
{
    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * @param AbstractToken[] $stack
     *
     * @return AbstractTokenScalar
     */
    public function execute(&$stack)
    {
        $stack[] = static::$pattern;
        return $this->calculator->callFunction('compare', $stack);
    }

}