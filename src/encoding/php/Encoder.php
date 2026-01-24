<?php
/**
 * QR3K Encoder - PHP implementation
 * Encoding chain: game code -> gzip -> xor -> base64
 */

class QR3KEncoder {
    const XOR_KEY = 'qr3k';
    const QR_LIMIT = 2953;
    const DECODER_SIZE = 180; // Approximate minified decoder size

    /**
     * Apply XOR cipher with repeating key
     * @param string $data - Data to encrypt/decrypt
     * @param string $key - Encryption key (defaults to "qr3k")
     * @return string XOR-encrypted data
     */
    public static function xorWithKey($data, $key = self::XOR_KEY) {
        $result = '';
        $keyLen = strlen($key);
        $dataLen = strlen($data);

        for ($i = 0; $i < $dataLen; $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }

        return $result;
    }

    /**
     * Encode game code with gzip + xor + base64
     * @param string $code - Game code (HTML or JavaScript)
     * @param array $options - Encoding options
     * @return array Encoding result with URLs and size info
     */
    public static function encode($code, $options = []) {
        $level = $options['level'] ?? 6; // Compression level (1-9)

        // Step 1: Gzip compress
        $compressed = gzencode($code, $level);
        if ($compressed === false) {
            return [
                'success' => false,
                'error' => 'Gzip compression failed'
            ];
        }

        // Step 2: XOR encrypt
        $encrypted = self::xorWithKey($compressed);

        // Step 3: Base64 encode
        $encoded = base64_encode($encrypted);

        // Step 4: URL encode for QR
        $urlSafe = urlencode($encoded);

        // Calculate sizes
        $rawSize = strlen($code);
        $compressedSize = strlen($compressed);
        $encryptedSize = strlen($encrypted); // Same as compressed
        $encodedSize = strlen($encoded);
        $urlSize = strlen($urlSafe);
        $totalSize = $encodedSize + self::DECODER_SIZE;

        // Check limits
        $isOverLimit = $totalSize > self::QR_LIMIT;

        // Generate URLs
        $gameUrl = "https://www.vincentbruijn.nl/qr3k/?z={$urlSafe}";
        $qrUrl = "https://cdn.vincentbruijn.nl/qr/img.php?q=" . urlencode($gameUrl);

        $compressionRatio = round((1 - $compressedSize / $rawSize) * 100, 1);

        return [
            'success' => true,
            'encoded' => $encoded,
            'gameUrl' => $gameUrl,
            'qrUrl' => $qrUrl,
            'size' => [
                'raw' => $rawSize,
                'compressed' => $compressedSize,
                'encrypted' => $encryptedSize,
                'base64' => $encodedSize,
                'url' => $urlSize,
                'decoder' => self::DECODER_SIZE,
                'total' => $totalSize,
                'limit' => self::QR_LIMIT,
                'isOverLimit' => $isOverLimit,
                'remaining' => self::QR_LIMIT - $totalSize,
                'compressionRatio' => $compressionRatio . '%',
                'savings' => $rawSize - $compressedSize
            ],
            'metadata' => [
                'method' => 'gzip+xor+base64',
                'gzipLevel' => $level,
                'timestamp' => date('c')
            ]
        ];
    }

    /**
     * Legacy XOR-only encoding (for backward compatibility)
     * @param string $code - Game code
     * @return array Encoding result
     */
    public static function encodeXOROnly($code) {
        // XOR + Base64 (legacy method)
        $encrypted = self::xorWithKey($code);
        $encoded = base64_encode($encrypted);
        $urlSafe = urlencode($encoded);

        $rawSize = strlen($code);
        $encodedSize = strlen($encoded);

        $gameUrl = "https://www.vincentbruijn.nl/qr3k/?x={$urlSafe}";
        $qrUrl = "https://cdn.vincentbruijn.nl/qr/img.php?q=" . urlencode($gameUrl);

        return [
            'success' => true,
            'encoded' => $encoded,
            'gameUrl' => $gameUrl,
            'qrUrl' => $qrUrl,
            'size' => [
                'raw' => $rawSize,
                'base64' => $encodedSize,
                'limit' => self::QR_LIMIT,
                'isOverLimit' => $encodedSize > self::QR_LIMIT,
                'remaining' => self::QR_LIMIT - $encodedSize
            ],
            'metadata' => [
                'method' => 'xor+base64',
                'timestamp' => date('c')
            ]
        ];
    }

    /**
     * Compare encoding methods
     * @param string $code - Game code
     * @return array Comparison of different encoding methods
     */
    public static function compare($code) {
        $gzipResult = self::encode($code);
        $xorResult = self::encodeXOROnly($code);

        $rawSize = strlen($code);
        $gzipTotal = $gzipResult['size']['total'];
        $xorTotal = $xorResult['size']['base64'];
        $savings = $xorTotal - $gzipTotal;
        $improvement = round(($savings / $xorTotal) * 100, 1);

        return [
            'raw' => $rawSize,
            'methods' => [
                'gzip+xor+base64' => [
                    'total' => $gzipTotal,
                    'savings' => $savings,
                    'improvement' => $improvement . '%'
                ],
                'xor+base64' => [
                    'total' => $xorTotal,
                    'savings' => 0,
                    'improvement' => '0%'
                ]
            ],
            'recommended' => $gzipTotal < $xorTotal ? 'gzip+xor+base64' : 'xor+base64'
        ];
    }
}
