<?php
/**
 * This file is part of the AceCalculator package
 * https://github.com/aVadim483/AceCalculator
 *
 */

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'avadim\\AceCalculator\\')) {
        include __DIR__ . '/' . str_replace('avadim\\AceCalculator\\', '/', $class) . '.php';
    }
});

// EOF