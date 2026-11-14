<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'ZPanel\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen('ZPanel\\'))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
