<?php
/**
 * QR3K QR Image Endpoint
 *
 * Self-hosted replacement for the external QR service, powered by a vendored
 * chillerlan/php-qrcode (no Composer — see src/encoding/vendor/).
 *
 * GET qr.php?d=<game-url>          Render a QR code for a QR3K game URL
 *   &format=svg|png                Output format (default: svg; png needs GD)
 *   &fg=RRGGBB                     Module color   (default: 0a0a0a)
 *   &bg=RRGGBB                     Background     (default: ffd700)
 *
 * Only URLs pointing at the QR3K runtime are rendered — this is not a
 * general-purpose QR service.
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once __DIR__ . '/ratelimit.php';

// Vendored libraries live one level above the web root in the Docker image,
// or inside it when deployed via plain file copy (same layout as api.php).
$autoloadPaths = [
    __DIR__ . '/../encoding/vendor/autoload.php',
    __DIR__ . '/encoding/vendor/autoload.php',
];
foreach ($autoloadPaths as $autoloadPath) {
    if (is_file($autoloadPath)) {
        require_once $autoloadPath;
        break;
    }
}

// The QR code encodes a full game URL, which always points at the production
// runtime — even when this endpoint runs on localhost during development.
const ALLOWED_PREFIXES = [
    'https://www.vincentbruijn.nl/qr3k/',
];
const QR_LIMIT = 2953; // version 40, binary mode, ECC L

function fail($status, $message) {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

/** @return array{0:int,1:int,2:int}|null RGB triplet, or null if invalid */
function parseHexColor($hex) {
    if (!preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $m)) {
        return null;
    }
    return array_map('hexdec', str_split($m[1], 2));
}

/** Perceived luminance 0..1 — cheap scannability guard, not color science */
function luminance(array $rgb) {
    return (0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2]) / 255;
}

if (!class_exists('chillerlan\\QRCode\\QRCode')) {
    error_log('QR3K: vendored php-qrcode not found in any known location');
    fail(500, 'Server misconfiguration. Please try again later.');
}

// More generous than api.php: the encode page loads one image per encode call
if (!qr3kRateLimitAllows('qr', 60, 60)) {
    header('Retry-After: 60');
    fail(429, 'Too many requests. Take a breath, then golf on.');
}

$data = $_GET['d'] ?? '';

$allowed = false;
foreach (ALLOWED_PREFIXES as $prefix) {
    if (str_starts_with($data, $prefix)) {
        $allowed = true;
        break;
    }
}
if (!$allowed) {
    fail(400, 'Only QR3K game URLs are rendered here. Pass one via ?d=');
}
if (strlen($data) > QR_LIMIT) {
    fail(400, 'URL exceeds QR code capacity (' . QR_LIMIT . ' bytes).');
}

$format = $_GET['format'] ?? 'svg';
if (!in_array($format, ['svg', 'png'], true)) {
    fail(400, 'Invalid format. Use "svg" or "png".');
}
if ($format === 'png' && !extension_loaded('gd')) {
    fail(501, 'PNG output requires the GD extension. Use format=svg.');
}

$fg = parseHexColor($_GET['fg'] ?? '0a0a0a');
$bg = parseHexColor($_GET['bg'] ?? 'ffd700');
if ($fg === null || $bg === null) {
    fail(400, 'Colors must be 6-digit hex, e.g. fg=0a0a0a&bg=ffd700');
}

// Scanners want dark modules on a light background with real contrast
if (luminance($bg) - luminance($fg) < 0.3) {
    fail(400, 'Not scannable: fg must be clearly darker than bg. Try more contrast.');
}

// Color every layer: dark modules get fg, light modules (incl. quiet zone)
// get bg. SVG wants color strings, GD wants RGB triplets.
$moduleValues = [];
foreach (array_keys(QROutputInterface::DEFAULT_MODULE_VALUES) as $layer) {
    $isDark = ($layer & QRMatrix::IS_DARK) === QRMatrix::IS_DARK;
    $rgb = $isDark ? $fg : $bg;
    $moduleValues[$layer] = $format === 'png'
        ? $rgb
        : sprintf('#%02x%02x%02x', ...$rgb);
}

$options = new QROptions([
    'eccLevel' => EccLevel::L, // max byte capacity — a logo would need Q/H
    'outputType' => $format === 'png'
        ? QROutputInterface::GDIMAGE_PNG
        : QROutputInterface::MARKUP_SVG,
    'outputBase64' => false,
    'moduleValues' => $moduleValues,
    'drawLightModules' => true,
    'quietzoneSize' => 4,
    'scale' => 8,
]);

try {
    $image = (new QRCode($options))->render($data);
} catch (Throwable $e) {
    error_log('QR3K qr.php render error: ' . $e->getMessage());
    fail(500, 'QR rendering failed. Please try again.');
}

header('Content-Type: ' . ($format === 'png' ? 'image/png' : 'image/svg+xml'));
// The URL fully determines the image, so cache hard
header('Cache-Control: public, max-age=31536000, immutable');
echo $image;
