<?php

declare(strict_types=1);

$directory = __DIR__;

while (!file_exists($directory . '/vendor/autoload.php')) {
    $parent = dirname($directory);

    if ($parent === $directory) {
        throw new \RuntimeException(
            'Unable to locate vendor/autoload.php, run the tests from inside a Magento project'
        );
    }

    $directory = $parent;
}

require $directory . '/vendor/autoload.php';
