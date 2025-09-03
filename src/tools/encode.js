const { encode } = require('../runtime/xor.js');

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

// XOR encode both examples
const htmlEncoded = encode(htmlGame);
const jsEncoded = encode(jsGame);

const htmlURL = `https://www.vincentbruijn.nl/qr3k/?x=${encodeURIComponent(htmlEncoded)}`;
const jsURL = `https://www.vincentbruijn.nl/qr3k/?x=${encodeURIComponent(jsEncoded)}`;

const htmlQrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(htmlURL)}`;
const jsQrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(jsURL)}`;

console.log('HTML Game (auto-detected as HTML, injected into DOM):');
console.log(htmlQrUrl);
console.log('');
console.log('JavaScript Game (auto-detected as JS, executed directly):');
console.log(jsQrUrl);
console.log('');
