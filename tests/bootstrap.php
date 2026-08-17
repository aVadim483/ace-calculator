<?php

$autoloadFiles = [
    // the package is the root project
    __DIR__ . '/../vendor/autoload.php',
    // the package is installed as a dependency
    __DIR__ . '/../../../autoload.php',
];

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;

        return;
    }
}

throw new \RuntimeException('Composer autoload not found, run "composer install" first');
