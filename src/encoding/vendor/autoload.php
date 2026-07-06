<?php
/**
 * Minimal PSR-4 autoloader for the vendored QR code libraries.
 *
 * This replaces Composer's autoloader — the libraries are plain PSR-4 PHP,
 * so mapping their namespaces to the vendored src/ directories is all the
 * "installation" they need. See VERSIONS for the pinned release tags.
 */

spl_autoload_register(function (string $class): void {
    static $prefixes = [
        'chillerlan\\QRCode\\'   => __DIR__ . '/php-qrcode/src/',
        'chillerlan\\Settings\\' => __DIR__ . '/settings-container/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});
