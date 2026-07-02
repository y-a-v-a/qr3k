/**
 * QR3K encoding demo tool
 * Usage: node src/tools/encode.js [path-to-game-file]
 * Without arguments it encodes two small example games.
 */

const fs = require('fs');
const { encode, encodeXOROnly } = require('../encoding/node/encoder');

// HTML game example
const htmlGame = `<h1>QR3K Test</h1><p>It works!</p><canvas id="c" width="300" height="200"></canvas>
<script>
const c = document.getElementById('c').getContext('2d');
c.fillStyle = '#0f0';
c.fillRect(10, 10, 280, 180);
c.fillStyle = '#000';
c.font = '20px monospace';
c.fillText('HTML QR3K Game!', 20, 110);
</script>`;

// JavaScript game example
const jsGame = `
const h = document.createElement('h1');
h.textContent = 'QR3K JS Test';
document.body.appendChild(h);

const c = document.createElement('canvas');
c.width = 300;
c.height = 200;
c.style.border = '1px solid white';
document.body.appendChild(c);

const ctx = c.getContext('2d');
ctx.fillStyle = '#0a0';
ctx.fillRect(50, 50, 200, 100);
ctx.fillStyle = '#fff';
ctx.font = '24px monospace';
ctx.fillText('JS QR3K Game!', 60, 110);
`;

async function report(label, code) {
  const gzip = await encode(code);
  const xor = encodeXOROnly(code);

  console.log(`${label}:`);
  console.log(`  Gzip URL (${gzip.size.total}/${gzip.size.limit} bytes): ${gzip.qrUrl}`);
  console.log(`  XOR URL  (${xor.size.total}/${xor.size.limit} bytes): ${xor.qrUrl}`);
  if (gzip.size.isOverLimit) {
    console.log(`  ⚠️  Over the QR limit by ${-gzip.size.remaining} bytes`);
  }
  console.log('');
}

(async function main() {
  const file = process.argv[2];
  if (file) {
    const code = fs.readFileSync(file, 'utf8');
    await report(file, code);
    return;
  }

  await report('HTML Game (auto-detected as HTML, injected into DOM)', htmlGame);
  await report('JavaScript Game (auto-detected as JS, executed directly)', jsGame);
})();
