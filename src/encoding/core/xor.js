/**
 * XOR encryption/decryption utilities
 * Uses "qr3k" as the repeating key for obfuscation
 */

const XOR_KEY = 'qr3k';

/**
 * Apply XOR cipher with repeating key
 * @param {string|Buffer} data - Data to encrypt/decrypt
 * @param {string} key - Encryption key (defaults to "qr3k")
 * @returns {Buffer} XOR-encrypted data
 */
function xorWithKey(data, key = XOR_KEY) {
  const input = Buffer.isBuffer(data) ? data : Buffer.from(data, 'binary');
  const keyBuf = Buffer.from(key, 'utf8');
  const result = Buffer.alloc(input.length);

  for (let i = 0; i < input.length; i++) {
    result[i] = input[i] ^ keyBuf[i % keyBuf.length];
  }

  return result;
}

/**
 * XOR encrypt and convert to base64
 * @param {string|Buffer} data - Data to encode
 * @returns {string} Base64-encoded XOR-encrypted data
 */
function encodeXOR(data) {
  const encrypted = xorWithKey(data);
  return encrypted.toString('base64');
}

/**
 * Decode base64 and XOR decrypt
 * @param {string} encoded - Base64-encoded XOR-encrypted data
 * @returns {Buffer} Decrypted data
 */
function decodeXOR(encoded) {
  const encrypted = Buffer.from(encoded, 'base64');
  return xorWithKey(encrypted);
}

module.exports = {
  XOR_KEY,
  xorWithKey,
  encodeXOR,
  decodeXOR
};
