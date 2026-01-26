<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>QR Game</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body class="game-runtime">
    <div id="container"></div>
    <div id="error" style="display: none">
      No game data found. Add ?z=gzip-encoded-data or ?x=xor-encoded-data to URL
    </div>

    <script>
      (async function() {
        try {
          // Extract game parameter from URL
          const params = new URLSearchParams(window.location.search);
          const gzipData = params.get('z'); // New: Gzip+XOR+Base64
          const xorData = params.get('x');  // Legacy: XOR+Base64

          if (gzipData) {
            // Decode gzip+xor+base64 (new method)
            // Inline decoder to avoid extra HTTP request
            const base64Decoded = atob(decodeURIComponent(gzipData));
            const key = 'qr3k';
            let xorDecrypted = '';
            for (let i = 0; i < base64Decoded.length; i++) {
              xorDecrypted += String.fromCharCode(
                base64Decoded.charCodeAt(i) ^ key.charCodeAt(i % 4)
              );
            }
            const bytes = new Uint8Array(xorDecrypted.length);
            for (let i = 0; i < xorDecrypted.length; i++) {
              bytes[i] = xorDecrypted.charCodeAt(i);
            }
            const stream = new Blob([bytes]).stream();
            const decompressedStream = stream.pipeThrough(new DecompressionStream('gzip'));
            const response = new Response(decompressedStream);
            const code = await response.text();

            // Execute decoded code
            eval(code);

          } else if (xorData) {
            // Legacy XOR-only decoding - load xor.js
            const script = document.createElement('script');
            script.src = 'xor.js';
            script.onload = function() {
              const decoded = window.xor.decode(decodeURIComponent(xorData));
              window.xor.executeContent(decoded);
            };
            document.head.appendChild(script);

          } else {
            // Show error if no game data
            document.getElementById('error').style.display = 'block';
          }
        } catch (e) {
          // Handle decoding errors
          console.error('Decode error:', e);
          document.getElementById('error').innerHTML =
            'Invalid game data: ' + e.message;
          document.getElementById('error').style.display = 'block';
        }
      })();
    </script>
  </body>
</html>
