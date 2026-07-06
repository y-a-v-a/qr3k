/**
 * QR3K Encoder - Node.js implementation
 * Encoding chain: game code -> gzip -> xor -> base64
 */

const zlib = require('zlib');
const { xorWithKey } = require('../core/xor');

// Max capacity of a QR code (version 40, binary mode, error correction L).
// The QR code contains the FULL game URL, so that is what we measure.
const QR_LIMIT = 2953;
const RUNTIME_URL = 'https://www.vincentbruijn.nl/qr3k/';
// Self-hosted QR endpoint next to the runtime (see src/runtime/qr.php)
const QR_IMAGE_URL = `${RUNTIME_URL}qr.php?d=`;

/**
 * Encode game code with gzip + xor + base64
 * @param {string} code - Game code (HTML or JavaScript)
 * @param {object} options - Encoding options
 * @param {number} options.level - Gzip compression level (1-9, default 6)
 * @returns {Promise<object>} Encoding result with URLs and size info
 */
async function encode(code, options = {}) {
  const { level = 6 } = options;

  // Step 1: Gzip compress
  const compressed = await new Promise((resolve, reject) => {
    zlib.gzip(Buffer.from(code, 'utf8'), { level }, (err, result) => {
      if (err) reject(err);
      else resolve(result);
    });
  });

  // Step 2: XOR obfuscate
  const encrypted = xorWithKey(compressed);

  // Step 3: Base64 encode
  const encoded = encrypted.toString('base64');

  // Step 4: URL encode for QR
  const urlSafe = encodeURIComponent(encoded);

  // Generate URLs
  const gameUrl = `${RUNTIME_URL}?z=${urlSafe}`;
  const qrUrl = `${QR_IMAGE_URL}${encodeURIComponent(gameUrl)}`;

  // Calculate sizes. The QR code encodes the full game URL, so the size
  // that matters is gameUrl.length — not the base64 payload.
  const rawSize = Buffer.byteLength(code, 'utf8');
  const compressedSize = compressed.length;
  const encodedSize = encoded.length;
  const totalSize = gameUrl.length;
  const isOverLimit = totalSize > QR_LIMIT;

  return {
    success: true,
    encoded,
    gameUrl,
    qrUrl,
    size: {
      raw: rawSize,
      compressed: compressedSize,
      base64: encodedSize,
      url: urlSafe.length,
      total: totalSize,
      limit: QR_LIMIT,
      isOverLimit,
      remaining: QR_LIMIT - totalSize,
      compressionRatio: rawSize > 0
        ? ((1 - compressedSize / rawSize) * 100).toFixed(1) + '%'
        : '0%',
      savings: rawSize - compressedSize
    },
    metadata: {
      method: 'gzip+xor+base64',
      gzipLevel: level,
      timestamp: new Date().toISOString()
    }
  };
}

/**
 * Legacy XOR-only encoding (for backward compatibility)
 * @param {string} code - Game code
 * @returns {object} Encoding result
 */
function encodeXOROnly(code) {
  // XOR + Base64 (legacy method)
  const encrypted = xorWithKey(Buffer.from(code, 'utf8'));
  const encoded = encrypted.toString('base64');
  const urlSafe = encodeURIComponent(encoded);

  const gameUrl = `${RUNTIME_URL}?x=${urlSafe}`;
  const qrUrl = `${QR_IMAGE_URL}${encodeURIComponent(gameUrl)}`;

  const rawSize = Buffer.byteLength(code, 'utf8');
  const encodedSize = encoded.length;
  const totalSize = gameUrl.length;

  return {
    success: true,
    encoded,
    gameUrl,
    qrUrl,
    size: {
      raw: rawSize,
      base64: encodedSize,
      url: urlSafe.length,
      total: totalSize,
      limit: QR_LIMIT,
      isOverLimit: totalSize > QR_LIMIT,
      remaining: QR_LIMIT - totalSize
    },
    metadata: {
      method: 'xor+base64',
      timestamp: new Date().toISOString()
    }
  };
}

/**
 * Compare encoding methods
 * @param {string} code - Game code
 * @returns {Promise<object>} Comparison of different encoding methods
 */
async function compare(code) {
  const gzipResult = await encode(code);
  const xorResult = encodeXOROnly(code);

  const gzipTotal = gzipResult.size.total;
  const xorTotal = xorResult.size.total;

  return {
    raw: Buffer.byteLength(code, 'utf8'),
    methods: {
      'gzip+xor+base64': {
        total: gzipTotal,
        savings: xorTotal - gzipTotal,
        improvement: (((xorTotal - gzipTotal) / xorTotal) * 100).toFixed(1) + '%'
      },
      'xor+base64': {
        total: xorTotal,
        savings: 0,
        improvement: '0%'
      }
    },
    recommended: gzipTotal < xorTotal ? 'gzip+xor+base64' : 'xor+base64'
  };
}

module.exports = {
  encode,
  encodeXOROnly,
  compare
};
