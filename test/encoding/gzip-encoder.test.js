/**
 * Tests for QR3K Gzip+XOR+Base64 Encoder
 */

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

  // XOR decrypt
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
  test('XOR encryption and decryption are symmetric', () => {
    const original = 'Hello, QR3K!';
    const encrypted = xorWithKey(Buffer.from(original, 'utf8'));
    const decrypted = xorWithKey(encrypted);
    expect(decrypted.toString('utf8')).toBe(original);
  });

  test('encodeXOR and decodeXOR are inverse operations', () => {
    const original = 'test data 123';
    const encoded = encodeXOR(original);
    const decoded = decodeXOR(encoded);
    expect(decoded.toString('utf8')).toBe(original);
  });

  test('XOR with binary data works correctly', () => {
    const binary = Buffer.from([0, 1, 2, 3, 255, 254, 253]);
    const encrypted = xorWithKey(binary);
    const decrypted = xorWithKey(encrypted);
    expect(decrypted).toEqual(binary);
  });
});

describe('Gzip+XOR+Base64 Encoder', () => {
  test('encode returns valid result structure', async () => {
    const result = await encode(testGames.minimal);

    expect(result.success).toBe(true);
    expect(result.encoded).toBeDefined();
    expect(result.gameUrl).toBeDefined();
    expect(result.qrUrl).toBeDefined();
    expect(result.size).toBeDefined();
    expect(result.metadata).toBeDefined();
  });

  test('encoded data can be decoded back to original', async () => {
    const original = testGames.minimal;
    const result = await encode(original);
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe(original);
  });

  test('gzip compression provides size savings', async () => {
    const original = testGames.snake;
    const result = await encode(original);

    // Compression should reduce size
    expect(result.size.compressed).toBeLessThan(result.size.raw);

    // Compression ratio should be reasonable (at least 20% for this code)
    const ratio = (result.size.compressed / result.size.raw);
    expect(ratio).toBeLessThan(0.8);
  });

  test('size calculations are accurate', async () => {
    const result = await encode(testGames.minimal);

    expect(result.size.raw).toBe(Buffer.byteLength(testGames.minimal, 'utf8'));
    expect(result.size.base64).toBe(result.encoded.length);
    expect(result.size.total).toBe(result.size.base64 + result.size.decoder);
  });

  test('compression levels affect size', async () => {
    const code = testGames.snake;

    const level1 = await encode(code, { level: 1 }); // Fast
    const level9 = await encode(code, { level: 9 }); // Best

    // Level 9 should produce smaller output
    expect(level9.size.compressed).toBeLessThanOrEqual(level1.size.compressed);
  });

  test('QR limit detection works', async () => {
    const largeCode = 'x'.repeat(5000);
    const result = await encode(largeCode);

    expect(result.size.isOverLimit).toBe(true);
    expect(result.size.remaining).toBeLessThan(0);
  });

  test('gameUrl uses ?z= parameter', async () => {
    const result = await encode(testGames.minimal);

    expect(result.gameUrl).toContain('?z=');
    expect(result.gameUrl).toContain('www.vincentbruijn.nl/qr3k');
  });

  test('qrUrl is properly formatted', async () => {
    const result = await encode(testGames.minimal);

    expect(result.qrUrl).toContain('cdn.vincentbruijn.nl/qr/img.php?q=');
    expect(result.qrUrl).toContain(encodeURIComponent('https://'));
  });
});

describe('Legacy XOR-only Encoder', () => {
  test('encodeXOROnly returns valid result', () => {
    const result = encodeXOROnly(testGames.minimal);

    expect(result.success).toBe(true);
    expect(result.encoded).toBeDefined();
    expect(result.gameUrl).toContain('?x=');
  });

  test('encodeXOROnly produces larger output than gzip', async () => {
    const code = testGames.snake;

    const xorResult = encodeXOROnly(code);
    const gzipResult = await encode(code);

    expect(gzipResult.size.total).toBeLessThan(xorResult.size.base64);
  });
});

describe('Encoding Comparison', () => {
  test('compare returns valid comparison', async () => {
    const result = await compare(testGames.snake);

    expect(result.raw).toBeDefined();
    expect(result.methods).toBeDefined();
    expect(result.methods['gzip+xor+base64']).toBeDefined();
    expect(result.methods['xor+base64']).toBeDefined();
    expect(result.recommended).toBeDefined();
  });

  test('gzip+xor+base64 is recommended for larger code', async () => {
    const result = await compare(testGames.snake);

    expect(result.recommended).toBe('gzip+xor+base64');
    expect(result.methods['gzip+xor+base64'].savings).toBeGreaterThan(0);
  });

  test('savings calculation is accurate', async () => {
    const code = testGames.snake;
    const result = await compare(code);

    const xorSize = result.methods['xor+base64'].total;
    const gzipSize = result.methods['gzip+xor+base64'].total;
    const expectedSavings = xorSize - gzipSize;

    expect(result.methods['gzip+xor+base64'].savings).toBe(expectedSavings);
  });
});

describe('Edge Cases', () => {
  test('handles empty string', async () => {
    const result = await encode('');
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe('');
  });

  test('handles very small code', async () => {
    const result = await encode('x=1');
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe('x=1');
  });

  test('handles special characters', async () => {
    const code = 'const msg = "Hello 世界! 🎮";';
    const result = await encode(code);
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe(code);
  });

  test('handles HTML content', async () => {
    const html = '<canvas id="c"></canvas><script>alert("game")</script>';
    const result = await encode(html);
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe(html);
  });

  test('handles newlines and whitespace', async () => {
    const code = 'line1\nline2\r\nline3\t\ttab';
    const result = await encode(code);
    const decoded = await decodeGzip(result.encoded);

    expect(decoded).toBe(code);
  });
});

describe('Performance', () => {
  test('encoding completes quickly', async () => {
    const start = Date.now();
    await encode(testGames.snake);
    const duration = Date.now() - start;

    // Should complete in under 100ms
    expect(duration).toBeLessThan(100);
  });

  test('multiple encodings in sequence', async () => {
    const promises = [
      encode(testGames.minimal),
      encode(testGames.snake),
      encode(testGames.minimal)
    ];

    const results = await Promise.all(promises);

    expect(results).toHaveLength(3);
    results.forEach(result => {
      expect(result.success).toBe(true);
    });
  });
});
