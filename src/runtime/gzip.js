// QR3K Gzip Decoder - Runtime decompression utilities
// Decoding chain: base64 -> xor -> gzip decompress -> execute

/**
 * Decode and execute gzip+xor+base64 encoded content
 * @param {string} encoded - Base64-encoded encrypted compressed data
 */
async function decodeAndExecute(encoded) {
  try {
    // Step 1: Base64 decode
    const base64Decoded = atob(encoded);

    // Step 2: XOR decrypt with "qr3k" key
    const key = 'qr3k';
    let decrypted = '';
    for (let i = 0; i < base64Decoded.length; i++) {
      decrypted += String.fromCharCode(
        base64Decoded.charCodeAt(i) ^ key.charCodeAt(i % 4)
      );
    }

    // Step 3: Convert to Uint8Array for decompression
    const bytes = new Uint8Array(decrypted.length);
    for (let i = 0; i < decrypted.length; i++) {
      bytes[i] = decrypted.charCodeAt(i);
    }

    // Step 4: Gzip decompress using DecompressionStream
    const stream = new Blob([bytes]).stream();
    const decompressedStream = stream.pipeThrough(
      new DecompressionStream('gzip')
    );
    const response = new Response(decompressedStream);
    const decompressed = await response.text();

    // Step 5: Execute the decompressed code
    eval(decompressed);

  } catch (error) {
    console.error('Decode error:', error);
    document.body.innerHTML = `<div style="padding:20px;color:#f00">
      <h1>Decode Error</h1>
      <p>${error.message}</p>
      <p>This game may be corrupted or use an incompatible encoding.</p>
    </div>`;
  }
}

// Minified version (use this in production for size optimization)
// Size: ~180 bytes minified
const decodeAndExecuteMinified = `async function d(e){try{let t=atob(e),n="qr3k",r="";for(let e=0;e<t.length;e++)r+=String.fromCharCode(t.charCodeAt(e)^n.charCodeAt(e%4));let o=new Uint8Array(r.length);for(let e=0;e<r.length;e++)o[e]=r.charCodeAt(e);let a=new Blob([o]).stream().pipeThrough(new DecompressionStream("gzip")),c=await new Response(a).text();eval(c)}catch(e){console.error("Decode error:",e),document.body.innerHTML='<div style="padding:20px;color:#f00"><h1>Decode Error</h1><p>'+e.message+"</p></div>"}}`;

// Export for Node.js testing
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { decodeAndExecute, decodeAndExecuteMinified };
} else if (typeof window !== 'undefined') {
  window.QR3K = window.QR3K || {};
  window.QR3K.decodeAndExecute = decodeAndExecute;
}
