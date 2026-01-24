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
 *     "total": 1180,
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

// Include the encoder class
require_once __DIR__ . '/../encoding/php/Encoder.php';

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
$method = $data['method'] ?? 'gzip'; // Default to gzip compression

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

            // Calculate compression ratio as a number
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
                        'totalBytes' => $xorResult['size']['base64'],
                        'base64Bytes' => $xorResult['size']['base64']
                    ]
                ]
            ];
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid method. Use "gzip", "xor", or "compare".'
            ]);
            exit;
    }

    // Add warning if over limit
    if (isset($response['size']) && $response['size']['isOverLimit']) {
        $overBy = abs($response['size']['remaining']);
        $response['warning'] = "Code size ({$response['size']['total']} bytes) exceeds QR code limit by {$overBy} bytes. QR code may not work.";
    } elseif (isset($response['size']) && $response['size']['total'] > 2200) {
        $response['warning'] = "Code size ({$response['size']['total']} bytes) is approaching the QR code limit. {$response['size']['remaining']} bytes remaining.";
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
