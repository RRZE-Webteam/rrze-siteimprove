<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

spl_autoload_register(function ($class) {
    $prefix = 'RRZE\Siteimprove\\';
    $base_dir = __DIR__ . '/RRZE/Siteimprove/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
