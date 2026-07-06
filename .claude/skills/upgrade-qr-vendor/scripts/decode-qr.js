// Decode a QR code PNG and print its data. Used by verify.sh for the
// round-trip check. Needs jsqr + pngjs installed next to it.
const fs = require('fs');
const { PNG } = require('pngjs');
const jsQR = require('jsqr');

const png = PNG.sync.read(fs.readFileSync(process.argv[2]));
const result = jsQR(new Uint8ClampedArray(png.data), png.width, png.height);
if (!result) {
  console.error('DECODE FAILED');
  process.exit(1);
}
console.log(result.data);
