<?php
/**
 * QR3K API Endpoint
 *
 * Accepts POST requests with JSON body containing game code
 * Returns JSON with QR code URL
 *
 * Request format:
 * {
 *   "code": "game code here (HTML or JavaScript)",
 *   "method": "gzip" | "xor" | "compare"  // Optional, defaults to "gzip"
 * }
 *
 * Response format (gzip method):
 * {
 *   "success": true,
 *   "gameUrl": "https://www.vincentbruijn.nl/qr3k/?z=...",
 *   "qrUrl": "https://cdn.vincentbruijn.nl/qr/img.php?q=...",
 *   "size": {
 *     "raw": 1234,
 *     "compressed": 750,
 *     "base64": 1000,
 *     "total": 1180,          // full game URL length — what the QR actually encodes
 *     "limit": 2953,
 *     "isOverLimit": false,
 *     "compressionRatio": "39.2%",
 *     "savings": 484
 *   },
 *   "metadata": {
 *     "method": "gzip+xor+base64",
 *     "gzipLevel": 6
 *   }
 * }
 */

// Include the encoder class. The library lives one level above the web root
// in the Docker image, or inside it when deployed via plain file copy.
$encoderPaths = [
    __DIR__ . '/../encoding/php/Encoder.php',
    __DIR__ . '/encoding/php/Encoder.php',
];
foreach ($encoderPaths as $encoderPath) {
    if (is_file($encoderPath)) {
        require_once $encoderPath;
        break;
    }
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Anything larger can never fit in a QR code anyway; this cap just keeps the
// endpoint from being used as a free general-purpose compression service.
const MAX_BODY_BYTES = 65536;
const RATE_LIMIT_REQUESTS = 30;   // per window, per IP
const RATE_LIMIT_WINDOW = 60;     // seconds

/**
 * Small file-based per-IP rate limiter. Playful project, boring throttle.
 * @return bool true if the request is allowed
 */
function rateLimitAllows() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/qr3k-ratelimit-' . hash('sha256', $ip);
    $now = time();

    $timestamps = [];
    if (is_file($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $t = (int) $line;
            if ($t > $now - RATE_LIMIT_WINDOW) {
                $timestamps[] = $t;
            }
        }
    }

    if (count($timestamps) >= RATE_LIMIT_REQUESTS) {
        return false;
    }

    $timestamps[] = $now;
    @file_put_contents($file, implode("\n", $timestamps), LOCK_EX);
    return true;
}

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
}

if (!class_exists('QR3KEncoder')) {
    error_log('QR3K: Encoder.php not found in any known location');
    respond(500, [
        'success' => false,
        'error' => 'Server misconfiguration. Please try again later.'
    ]);
}

if (!rateLimitAllows()) {
    header('Retry-After: ' . RATE_LIMIT_WINDOW);
    respond(429, [
        'success' => false,
        'error' => 'Too many requests. Take a breath, then golf on.'
    ]);
}

// Read JSON input (bounded — a real game is ~2KB, so 64KB is generous)
$json = file_get_contents('php://input', false, null, 0, MAX_BODY_BYTES + 1);
if (strlen($json) > MAX_BODY_BYTES) {
    respond(413, [
        'success' => false,
        'error' => 'Request too large. Games must fit in a QR code (2,953 bytes) — this is way past that.'
    ]);
}

$data = json_decode($json, true);

// Validate input
if (!is_array($data) || !isset($data['code']) || !is_string($data['code'])) {
    respond(400, [
        'success' => false,
        'error' => 'Invalid request. JSON body must contain "code" field.'
    ]);
}

$code = $data['code'];
$method = $data['method'] ?? 'gzip'; // Default to gzip compression

// Validate code is not empty
if (empty(trim($code))) {
    respond(400, [
        'success' => false,
        'error' => 'Code cannot be empty.'
    ]);
}

try {
    $response = null;

    switch ($method) {
        case 'gzip':
            // New gzip+xor+base64 encoding
            $response = QR3KEncoder::encode($code);
            break;

        case 'xor':
            // Legacy xor+base64 encoding
            $response = QR3KEncoder::encodeXOROnly($code);
            break;

        case 'compare':
            // Compare both methods - return full results for both
            $gzipResult = QR3KEncoder::encode($code);
            $xorResult = QR3KEncoder::encodeXOROnly($code);

            // Encoded-URL bytes per raw byte (< 1 means gzip beat the overhead)
            $compressionRatio = $gzipResult['size']['total'] / strlen($code);

            $response = [
                'success' => true,
                'rawSize' => strlen($code),
                'gzip' => [
                    'gameUrl' => $gzipResult['gameUrl'],
                    'qrUrl' => $gzipResult['qrUrl'],
                    'size' => [
                        'totalBytes' => $gzipResult['size']['total'],
                        'base64Bytes' => $gzipResult['size']['base64'],
                        'compressionRatio' => $compressionRatio
                    ]
                ],
                'xor' => [
                    'gameUrl' => $xorResult['gameUrl'],
                    'qrUrl' => $xorResult['qrUrl'],
                    'size' => [
                        'totalBytes' => $xorResult['size']['total'],
                        'base64Bytes' => $xorResult['size']['base64']
                    ]
                ]
            ];
            break;

        default:
            respond(400, [
                'success' => false,
                'error' => 'Invalid method. Use "gzip", "xor", or "compare".'
            ]);
    }

    // Add warning if over limit
    if (isset($response['size']) && $response['size']['isOverLimit']) {
        $overBy = abs($response['size']['remaining']);
        $response['warning'] = "Game URL ({$response['size']['total']} bytes) exceeds QR code limit by {$overBy} bytes. QR code may not work.";
    } elseif (isset($response['size']) && $response['size']['total'] > 2200) {
        $response['warning'] = "Game URL ({$response['size']['total']} bytes) is approaching the QR code limit. {$response['size']['remaining']} bytes remaining.";
    }

    respond(200, $response);

} catch (Throwable $e) {
    // Log details server-side, keep the client message generic
    error_log('QR3K encoding error: ' . $e->getMessage());
    respond(500, [
        'success' => false,
        'error' => 'Encoding failed. Please try again.'
    ]);
}
