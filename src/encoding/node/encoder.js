/**
 * QR3K Encoder - Node.js implementation
 * Encoding chain: game code -> gzip -> xor -> base64
 */

const zlib = require('zlib');
const { xorWithKey } = require('../core/xor');

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

  // Step 2: XOR encrypt
  const encrypted = xorWithKey(compressed);

  // Step 3: Base64 encode
  const encoded = encrypted.toString('base64');

  // Step 4: URL encode for QR
  const urlSafe = encodeURIComponent(encoded);

  // Calculate sizes
  const rawSize = Buffer.byteLength(code, 'utf8');
  const compressedSize = compressed.length;
  const encryptedSize = encrypted.length; // Same as compressed
  const encodedSize = encoded.length;
  const urlSize = urlSafe.length;

  // Estimate with decoder overhead
  const DECODER_SIZE = 180; // Approximate minified decoder size
  const totalSize = encodedSize + DECODER_SIZE;

  // Check limits
  const QR_LIMIT = 2953;
  const isOverLimit = totalSize > QR_LIMIT;

  // Generate URLs
  const gameUrl = `https://www.vincentbruijn.nl/qr3k/?z=${urlSafe}`;
  const qrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(gameUrl)}`;

  return {
    success: true,
    encoded,
    gameUrl,
    qrUrl,
    size: {
      raw: rawSize,
      compressed: compressedSize,
      encrypted: encryptedSize,
      base64: encodedSize,
      url: urlSize,
      decoder: DECODER_SIZE,
      total: totalSize,
      limit: QR_LIMIT,
      isOverLimit,
      remaining: QR_LIMIT - totalSize,
      compressionRatio: ((1 - compressedSize / rawSize) * 100).toFixed(1) + '%',
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
  const { xorWithKey } = require('../core/xor');

  // XOR + Base64 (legacy method)
  const encrypted = xorWithKey(Buffer.from(code, 'utf8'));
  const encoded = encrypted.toString('base64');
  const urlSafe = encodeURIComponent(encoded);

  const rawSize = Buffer.byteLength(code, 'utf8');
  const encodedSize = encoded.length;

  const gameUrl = `https://www.vincentbruijn.nl/qr3k/?x=${urlSafe}`;
  const qrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(gameUrl)}`;

  return {
    success: true,
    encoded,
    gameUrl,
    qrUrl,
    size: {
      raw: rawSize,
      base64: encodedSize,
      limit: 2953,
      isOverLimit: encodedSize > 2953,
      remaining: 2953 - encodedSize
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

  return {
    raw: Buffer.byteLength(code, 'utf8'),
    methods: {
      'gzip+xor+base64': {
        total: gzipResult.size.total,
        savings: xorResult.size.base64 - gzipResult.size.total,
        improvement: (((xorResult.size.base64 - gzipResult.size.total) / xorResult.size.base64) * 100).toFixed(1) + '%'
      },
      'xor+base64': {
        total: xorResult.size.base64,
        savings: 0,
        improvement: '0%'
      }
    },
    recommended: gzipResult.size.total < xorResult.size.base64 ? 'gzip+xor+base64' : 'xor+base64'
  };
}

module.exports = {
  encode,
  encodeXOROnly,
  compare
};
