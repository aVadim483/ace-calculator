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

namespace avadim\AceCalculator\Exception;

/**
 * Class UnknownIdentifier
 *
 * @package avadim\AceCalculator
 */
class UnknownIdentifier extends ExecException
{
    public function __construct(string $message = '', int $code = 0, $previous = null)
    {
        if ($code === null) {
            $code = self::CALC_UNKNOWN_IDENTIFIER;
        }
        parent::__construct($message, $code, $previous);
    }
}
