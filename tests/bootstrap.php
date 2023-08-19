<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

if (ini_get('zend.assertions') !== '1' || ini_get('assert.exception') !== '1') {
    echo 'The test suite requires that assertions are enabled via zend.assertions=1 and assert.exception=1.';

    die(1);
}
