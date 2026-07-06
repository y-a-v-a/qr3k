<?php
/**
 * Small file-based per-IP rate limiter. Playful project, boring throttle.
 * Shared by api.php (encoding) and qr.php (image rendering).
 */

/**
 * @param string $bucket Separate counter per endpoint so one can't starve the other
 * @return bool true if the request is allowed
 */
function qr3kRateLimitAllows($bucket, $maxRequests = 30, $window = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/qr3k-ratelimit-' . hash('sha256', $bucket . '|' . $ip);
    $now = time();

    $timestamps = [];
    if (is_file($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $t = (int) $line;
            if ($t > $now - $window) {
                $timestamps[] = $t;
            }
        }
    }

    if (count($timestamps) >= $maxRequests) {
        return false;
    }

    $timestamps[] = $now;
    @file_put_contents($file, implode("\n", $timestamps), LOCK_EX);
    return true;
}
