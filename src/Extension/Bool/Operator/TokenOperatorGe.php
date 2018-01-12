<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceCalculator\Extension\Bool\Operator;

/**
 * Class TokenOperatorGt
 *
 * @package avadim\AceCalculator
 */
class TokenOperatorGe extends TokenOperatorCompare
{
    protected static $pattern = '>=';

}