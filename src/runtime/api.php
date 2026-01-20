<?php
/**
 * QR3K API Endpoint
 *
 * Accepts POST requests with JSON body containing game code
 * Returns JSON with QR code URL
 *
 * Request format:
 * {
 *   "code": "game code here (HTML or JavaScript)"
 * }
 *
 * Response format:
 * {
 *   "success": true,
 *   "gameUrl": "https://www.vincentbruijn.nl/qr3k/?x=...",
 *   "qrUrl": "https://cdn.vincentbruijn.nl/qr/img.php?q=...",
 *   "size": {
 *     "bytes": 1234,
 *     "base64Bytes": 1646
 *   }
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

/**
 * XOR encrypt text with "qr3k" key
 * Matches the JavaScript xorWithKey function in xor.js
 */
function xorWithKey($text, $key) {
    $result = '';
    $keyLength = strlen($key);
    $textLength = strlen($text);

    for ($i = 0; $i < $textLength; $i++) {
        $textChar = ord($text[$i]);
        $keyChar = ord($key[$i % $keyLength]);
        $result .= chr($textChar ^ $keyChar);
    }

    return $result;
}

/**
 * Encode game code using XOR and base64
 * Matches the JavaScript encode function in xor.js
 */
function encodeGame($code) {
    $xorKey = 'qr3k';
    $xorEncrypted = xorWithKey($code, $xorKey);
    return base64_encode($xorEncrypted);
}

// Read JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!$data || !isset($data['code'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request. JSON body must contain "code" field.'
    ]);
    exit;
}

$code = $data['code'];

// Validate code is not empty
if (empty(trim($code))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Code cannot be empty.'
    ]);
    exit;
}

try {
    // Encode the game code
    $encoded = encodeGame($code);
    $urlSafe = urlencode($encoded);

    // Generate URLs
    $gameUrl = "https://www.vincentbruijn.nl/qr3k/?x={$urlSafe}";
    $qrUrl = "https://cdn.vincentbruijn.nl/qr/img.php?q=" . urlencode($gameUrl);

    // Calculate sizes
    $byteSize = strlen($code);
    $base64Size = strlen($encoded);

    // Check if size exceeds QR code limit
    $isOverLimit = $base64Size > 2953;
    $warning = null;

    if ($isOverLimit) {
        $warning = "Code size ({$base64Size} bytes) exceeds QR code limit of 2,953 bytes by " .
                   ($base64Size - 2953) . " bytes. QR code may not work.";
    } elseif ($base64Size > 2200) {
        $warning = "Code size ({$base64Size} bytes) is approaching the QR code limit. " .
                   (2953 - $base64Size) . " bytes remaining.";
    }

    // Success response
    $response = [
        'success' => true,
        'gameUrl' => $gameUrl,
        'qrUrl' => $qrUrl,
        'size' => [
            'bytes' => $byteSize,
            'base64Bytes' => $base64Size,
            'limit' => 2953,
            'isOverLimit' => $isOverLimit
        ]
    ];

    if ($warning) {
        $response['warning'] = $warning;
    }

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Encoding error: ' . $e->getMessage()
    ]);
}
