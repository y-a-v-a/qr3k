<?php
/**
 * QR3K Encoder - PHP implementation
 * Encoding chain: game code -> gzip -> xor -> base64
 */

class QR3KEncoder {
    const XOR_KEY = 'qr3k';
    // Max capacity of a QR code (version 40, binary mode, error correction L).
    // The QR code contains the FULL game URL, so that is what we measure.
    const QR_LIMIT = 2953;
    const RUNTIME_URL = 'https://www.vincentbruijn.nl/qr3k/';
    // Self-hosted QR endpoint (qr.php), relative so it works on localhost
    // during development and in production alike. The encoded game URL is
    // always the production RUNTIME_URL either way.
    const QR_IMAGE_URL = 'qr.php?d=';

    /**
     * Apply XOR cipher with repeating key.
     * This is obfuscation, not encryption — the key is public. It exists to
     * keep game code from being pattern-matched by iOS Safari's filtering.
     * @param string $data - Data to obfuscate/deobfuscate
     * @param string $key - Key (defaults to "qr3k")
     * @return string XOR-obfuscated data
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
     * @throws RuntimeException if compression fails
     */
    public static function encode($code, $options = []) {
        $level = $options['level'] ?? 6; // Compression level (1-9)

        // Step 1: Gzip compress
        $compressed = gzencode($code, $level);
        if ($compressed === false) {
            throw new RuntimeException('Gzip compression failed');
        }

        // Step 2: XOR obfuscate
        $encrypted = self::xorWithKey($compressed);

        // Step 3: Base64 encode
        $encoded = base64_encode($encrypted);

        // Step 4: URL encode for QR
        $urlSafe = urlencode($encoded);

        // Generate URLs
        $gameUrl = self::RUNTIME_URL . "?z={$urlSafe}";
        $qrUrl = self::QR_IMAGE_URL . urlencode($gameUrl);

        // Calculate sizes. The QR code encodes the full game URL, so the
        // size that matters is strlen($gameUrl) — not the base64 payload.
        $rawSize = strlen($code);
        $compressedSize = strlen($compressed);
        $encodedSize = strlen($encoded);
        $totalSize = strlen($gameUrl);
        $isOverLimit = $totalSize > self::QR_LIMIT;

        $compressionRatio = $rawSize > 0
            ? round((1 - $compressedSize / $rawSize) * 100, 1)
            : 0;

        return [
            'success' => true,
            'encoded' => $encoded,
            'gameUrl' => $gameUrl,
            'qrUrl' => $qrUrl,
            'size' => [
                'raw' => $rawSize,
                'compressed' => $compressedSize,
                'base64' => $encodedSize,
                'url' => strlen($urlSafe),
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

        $gameUrl = self::RUNTIME_URL . "?x={$urlSafe}";
        $qrUrl = self::QR_IMAGE_URL . urlencode($gameUrl);

        $rawSize = strlen($code);
        $encodedSize = strlen($encoded);
        $totalSize = strlen($gameUrl);

        return [
            'success' => true,
            'encoded' => $encoded,
            'gameUrl' => $gameUrl,
            'qrUrl' => $qrUrl,
            'size' => [
                'raw' => $rawSize,
                'base64' => $encodedSize,
                'url' => strlen($urlSafe),
                'total' => $totalSize,
                'limit' => self::QR_LIMIT,
                'isOverLimit' => $totalSize > self::QR_LIMIT,
                'remaining' => self::QR_LIMIT - $totalSize
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
        $xorTotal = $xorResult['size']['total'];
        $savings = $xorTotal - $gzipTotal;
        $improvement = $xorTotal > 0 ? round(($savings / $xorTotal) * 100, 1) : 0;

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
