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


class Container
{
    private $data;

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @param $object
     *
     * @return mixed
     */
    public function set($name, $object)
    {
        return $this->data[$name] = $object;
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (0 === strpos($method, 'get')) {
            $name = substr($method, 3);
            if ($this->has($name)) {
                return $this->get($name);
            }
        } elseif (0 === strpos($method, 'set')) {
            $name = substr($method, 3);
            return $this->set($name, reset($arguments));
        }
        return null;
    }

}

// EOF