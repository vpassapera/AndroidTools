#!/usr/bin/env php
<?php

if (defined('HHVM_VERSION_ID')) {
    if (HHVM_VERSION_ID < 30500) {
        fwrite(STDERR, "HHVM needs to be a minimum version of HHVM 3.5.0\n");
        exit(1);
    }
} elseif (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50306) {
    fwrite(STDERR, "PHP needs to be a minimum version of PHP 5.3.6\n");
    exit(1);
}

set_error_handler(function ($severity, $message, $file, $line) {
    if ($severity & error_reporting()) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

Phar::mapPhar('android-tools.phar');

require_once 'phar://android-tools.phar/vendor/autoload.php';

use Tdn\AndroidTools\Console\Application;

$application = new Application();
$application->run();

__HALT_COMPILER();
