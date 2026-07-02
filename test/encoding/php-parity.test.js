/**
 * PHP <-> JavaScript encoder parity tests
 *
 * The PHP encoder (api.php / Encoder.php) produces URLs that the browser
 * runtime decodes with JavaScript. If the two implementations drift, QR
 * codes silently break — so we encode with PHP and decode with the same
 * JS code paths the runtime uses.
 *
 * Requires the `php` CLI; tests are skipped when it is not installed.
 */

const { describe, test } = require('node:test');
const assert = require('node:assert');
const { execFileSync, spawnSync } = require('child_process');
const path = require('path');
const zlib = require('zlib');

const { xorWithKey } = require('../../src/encoding/core/xor');
const nodeEncoder = require('../../src/encoding/node/encoder');
const runtimeXor = require('../../src/runtime/xor.js');

const ENCODER_PHP = path.join(__dirname, '../../src/encoding/php/Encoder.php');
const phpAvailable = spawnSync('php', ['-v']).status === 0;
const skip = phpAvailable ? false : 'php CLI not available';

/**
 * Encode `code` using the real PHP encoder, return the parsed result array.
 * @param {string} code
 * @param {'gzip'|'xor'} method
 */
function phpEncode(code, method) {
  const script =
    "require $argv[1];" +
    "$code = stream_get_contents(STDIN);" +
    "$result = $argv[2] === 'xor'" +
    "  ? QR3KEncoder::encodeXOROnly($code)" +
    "  : QR3KEncoder::encode($code);" +
    "echo json_encode($result);";
  const out = execFileSync('php', ['-r', script, '--', ENCODER_PHP, method], {
    input: code,
    encoding: 'utf8'
  });
  return JSON.parse(out);
}

const sampleGame =
  'c=document.createElement("canvas");c.width=300;c.height=200;' +
  'document.body.appendChild(c);x=c.getContext("2d");' +
  'x.fillStyle="#0f0";x.fillRect(10,10,280,180);';

describe('PHP encoder -> JS runtime decoder parity', { skip }, () => {
  test('gzip method: PHP-encoded payload decodes back to the original', () => {
    const phpResult = phpEncode(sampleGame, 'gzip');

    // Same steps the runtime performs: base64 -> xor -> gunzip
    const decrypted = xorWithKey(Buffer.from(phpResult.encoded, 'base64'));
    const decoded = zlib.gunzipSync(decrypted).toString('utf8');

    assert.strictEqual(decoded, sampleGame);
  });

  test('gzip method: unicode content survives the round trip', () => {
    const code = 'x.fillText("Hello 世界! 🎮",10,10);';
    const phpResult = phpEncode(code, 'gzip');

    const decrypted = xorWithKey(Buffer.from(phpResult.encoded, 'base64'));
    const decoded = zlib.gunzipSync(decrypted).toString('utf8');

    assert.strictEqual(decoded, code);
  });

  test('xor method: PHP payload decodes with the browser xor.js decoder', () => {
    const phpResult = phpEncode(sampleGame, 'xor');

    // runtime/xor.js decode() is the exact code the browser runs (atob-based)
    const decoded = runtimeXor.decode(phpResult.encoded);

    assert.strictEqual(decoded, sampleGame);
  });

  test('xor method: PHP and Node encoders produce identical output', () => {
    const phpResult = phpEncode(sampleGame, 'xor');
    const nodeResult = nodeEncoder.encodeXOROnly(sampleGame);

    assert.strictEqual(phpResult.encoded, nodeResult.encoded);
    assert.strictEqual(phpResult.gameUrl, nodeResult.gameUrl);
    assert.strictEqual(phpResult.qrUrl, nodeResult.qrUrl);
  });

  test('gzip method: PHP and Node agree on size accounting', () => {
    const phpResult = phpEncode(sampleGame, 'gzip');
    assert.strictEqual(phpResult.size.total, phpResult.gameUrl.length);
    assert.strictEqual(phpResult.size.isOverLimit, phpResult.gameUrl.length > 2953);
  });
});
