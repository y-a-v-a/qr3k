/**
 * Tests for QR3K Gzip+XOR+Base64 Encoder
 * Runs with the built-in Node.js test runner: node --test test/encoding/
 */

const { describe, test } = require('node:test');
const assert = require('node:assert');

const { encode, encodeXOROnly, compare } = require('../../src/encoding/node/encoder');
const { xorWithKey, encodeXOR, decodeXOR } = require('../../src/encoding/core/xor');
const zlib = require('zlib');

// Test data
const testGames = {
  minimal: 'c=document.createElement("canvas");c.width=300;c.height=200;document.body.appendChild(c);',
  snake: `c=document.createElement('canvas')
c.width=300
c.height=300
document.body.appendChild(c)
x=c.getContext('2d')
s=[[15,15],[14,15],[13,15]]
d=0
fx=~~(Math.random()*30)
fy=~~(Math.random()*30)
sc=0
g=0
addEventListener('keydown',e=>{
k=e.keyCode
if(k==37&&d!=0)d=2
if(k==38&&d!=1)d=3
if(k==39&&d!=2)d=0
if(k==40&&d!=3)d=1
})
setInterval(_=>{
if(g)return
h=s[0]
nx=h[0]+(d==0?1:d==2?-1:0)
ny=h[1]+(d==1?1:d==3?-1:0)
if(nx<0||nx>29||ny<0||ny>29){g=1;return}
for(i=0;i<s.length;i++){if(s[i][0]==nx&&s[i][1]==ny){g=1;return}}
s.unshift([nx,ny])
if(nx==fx&&ny==fy){sc++;fx=~~(Math.random()*30);fy=~~(Math.random()*30)}else{s.pop()}
x.fillStyle='#000'
x.fillRect(0,0,300,300)
x.fillStyle='#0f0'
for(i=0;i<s.length;i++){x.fillRect(s[i][0]*10,s[i][1]*10,9,9)}
x.fillStyle='#ff0'
x.fillRect(fx*10,fy*10,9,9)
x.fillStyle='#fff'
x.fillText('Score: '+sc,5,15)
if(g){x.fillText('GAME OVER',100,150)}
},100)`
};

// Helper to decode gzip+xor+base64
async function decodeGzip(encoded) {
  // Base64 decode
  const base64Decoded = Buffer.from(encoded, 'base64');

  // XOR deobfuscate
  const decrypted = xorWithKey(base64Decoded);

  // Gunzip
  const decompressed = await new Promise((resolve, reject) => {
    zlib.gunzip(decrypted, (err, result) => {
      if (err) reject(err);
      else resolve(result.toString('utf8'));
    });
  });

  return decompressed;
}

describe('XOR Utilities', () => {
  test('XOR obfuscation is symmetric', () => {
    const original = 'Hello, QR3K!';
    const encrypted = xorWithKey(Buffer.from(original, 'utf8'));
    const decrypted = xorWithKey(encrypted);
    assert.strictEqual(decrypted.toString('utf8'), original);
  });

  test('encodeXOR and decodeXOR are inverse operations', () => {
    const original = 'test data 123';
    const encoded = encodeXOR(original);
    const decoded = decodeXOR(encoded);
    assert.strictEqual(decoded.toString('utf8'), original);
  });

  test('XOR with binary data works correctly', () => {
    const binary = Buffer.from([0, 1, 2, 3, 255, 254, 253]);
    const encrypted = xorWithKey(binary);
    const decrypted = xorWithKey(encrypted);
    assert.deepStrictEqual(decrypted, binary);
  });
});

describe('Gzip+XOR+Base64 Encoder', () => {
  test('encode returns valid result structure', async () => {
    const result = await encode(testGames.minimal);

    assert.strictEqual(result.success, true);
    assert.ok(result.encoded !== undefined);
    assert.ok(result.gameUrl !== undefined);
    assert.ok(result.qrUrl !== undefined);
    assert.ok(result.size !== undefined);
    assert.ok(result.metadata !== undefined);
  });

  test('encoded data can be decoded back to original', async () => {
    const original = testGames.minimal;
    const result = await encode(original);
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, original);
  });

  test('gzip compression provides size savings', async () => {
    const original = testGames.snake;
    const result = await encode(original);

    // Compression should reduce size
    assert.ok(result.size.compressed < result.size.raw);

    // Compression ratio should be reasonable (at least 20% for this code)
    const ratio = result.size.compressed / result.size.raw;
    assert.ok(ratio < 0.8);
  });

  test('size total is the full game URL length (what the QR encodes)', async () => {
    const result = await encode(testGames.minimal);

    assert.strictEqual(result.size.raw, Buffer.byteLength(testGames.minimal, 'utf8'));
    assert.strictEqual(result.size.base64, result.encoded.length);
    assert.strictEqual(result.size.total, result.gameUrl.length);
    assert.strictEqual(result.size.remaining, result.size.limit - result.size.total);
  });

  test('compression levels affect size', async () => {
    const code = testGames.snake;

    const level1 = await encode(code, { level: 1 }); // Fast
    const level9 = await encode(code, { level: 9 }); // Best

    // Level 9 should produce smaller output
    assert.ok(level9.size.compressed <= level1.size.compressed);
  });

  test('QR limit detection works', async () => {
    // Random bytes don't compress, so this stays over the limit after gzip
    const largeCode = require('crypto').randomBytes(4000).toString('hex');
    const result = await encode(largeCode);

    assert.strictEqual(result.size.isOverLimit, true);
    assert.ok(result.size.remaining < 0);
  });

  test('gameUrl uses ?z= parameter', async () => {
    const result = await encode(testGames.minimal);

    assert.ok(result.gameUrl.includes('?z='));
    assert.ok(result.gameUrl.includes('www.vincentbruijn.nl/qr3k'));
  });

  test('qrUrl is properly formatted', async () => {
    const result = await encode(testGames.minimal);

    assert.ok(result.qrUrl.includes('www.vincentbruijn.nl/qr3k/qr.php?d='));
    assert.ok(result.qrUrl.includes(encodeURIComponent('https://')));
  });
});

describe('Legacy XOR-only Encoder', () => {
  test('encodeXOROnly returns valid result', () => {
    const result = encodeXOROnly(testGames.minimal);

    assert.strictEqual(result.success, true);
    assert.ok(result.encoded !== undefined);
    assert.ok(result.gameUrl.includes('?x='));
  });

  test('encodeXOROnly size total is the full game URL length', () => {
    const result = encodeXOROnly(testGames.minimal);

    assert.strictEqual(result.size.total, result.gameUrl.length);
  });

  test('encodeXOROnly produces larger output than gzip', async () => {
    const code = testGames.snake;

    const xorResult = encodeXOROnly(code);
    const gzipResult = await encode(code);

    assert.ok(gzipResult.size.total < xorResult.size.total);
  });
});

describe('Encoding Comparison', () => {
  test('compare returns valid comparison', async () => {
    const result = await compare(testGames.snake);

    assert.ok(result.raw !== undefined);
    assert.ok(result.methods !== undefined);
    assert.ok(result.methods['gzip+xor+base64'] !== undefined);
    assert.ok(result.methods['xor+base64'] !== undefined);
    assert.ok(result.recommended !== undefined);
  });

  test('gzip+xor+base64 is recommended for larger code', async () => {
    const result = await compare(testGames.snake);

    assert.strictEqual(result.recommended, 'gzip+xor+base64');
    assert.ok(result.methods['gzip+xor+base64'].savings > 0);
  });

  test('savings calculation is accurate', async () => {
    const code = testGames.snake;
    const result = await compare(code);

    const xorSize = result.methods['xor+base64'].total;
    const gzipSize = result.methods['gzip+xor+base64'].total;
    const expectedSavings = xorSize - gzipSize;

    assert.strictEqual(result.methods['gzip+xor+base64'].savings, expectedSavings);
  });
});

describe('Edge Cases', () => {
  test('handles empty string', async () => {
    const result = await encode('');
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, '');
  });

  test('handles very small code', async () => {
    const result = await encode('x=1');
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, 'x=1');
  });

  test('handles special characters', async () => {
    const code = 'const msg = "Hello 世界! 🎮";';
    const result = await encode(code);
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, code);
  });

  test('handles HTML content', async () => {
    const html = '<canvas id="c"></canvas><script>alert("game")</script>';
    const result = await encode(html);
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, html);
  });

  test('handles newlines and whitespace', async () => {
    const code = 'line1\nline2\r\nline3\t\ttab';
    const result = await encode(code);
    const decoded = await decodeGzip(result.encoded);

    assert.strictEqual(decoded, code);
  });
});
