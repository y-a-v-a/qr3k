<?php
// Session exists only to bypass Varnish caching; keep its cookie out of
// script reach (games are sandboxed anyway, but defense in depth is free)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Content-Security-Policy for the runtime page. The sandboxed srcdoc iframe
// inherits this policy, so games keep canvas/keyboard/touch but cannot
// exfiltrate data (connect-src 'none') or load remote scripts.
header(
    "Content-Security-Policy: " .
    "default-src 'none'; " .
    "script-src 'self' 'unsafe-inline'; " .
    "style-src 'self' 'unsafe-inline'; " .
    "img-src 'self' data: blob:; " .
    "media-src data: blob:; " .
    "connect-src 'none'; " .
    "base-uri 'none'; " .
    "form-action 'none'"
);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>QR Game</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body class="game-runtime">
    <a href="about.php" class="game-overlay">QR3K</a>
    <div id="container"></div>
    <div id="error" style="display: none">
      No game data found. Add ?z=gzip-encoded-data or ?x=xor-encoded-data to URL
    </div>

    <script>
      (async function() {
        function showError(message) {
          const el = document.getElementById('error');
          el.textContent = message;
          el.style.display = 'block';
        }

        function isHtmlContent(content) {
          const trimmed = content.trim();
          return trimmed.startsWith('<') ||
            /<!DOCTYPE|<html|<head|<body|<script|<style|<div|<canvas/i.test(trimmed);
        }

        // Run the decoded game inside a sandboxed iframe: no same-origin
        // access, opaque origin, and it inherits this page's CSP. The game
        // can only play inside its own 3KB box.
        function runGame(code) {
          let html;
          if (isHtmlContent(code)) {
            html = code;
          } else {
            const safe = code.replace(/<\/script/gi, '<\\/script');
            html = '<body style="margin:0;background:#000;color:#fff">' +
              '<script>' + safe + '<\/script></body>';
          }
          const frame = document.createElement('iframe');
          frame.setAttribute('sandbox', 'allow-scripts');
          frame.className = 'game-frame';
          frame.style.cssText = 'border:0;width:100vw;height:100vh;display:block';
          frame.srcdoc = html;
          document.getElementById('container').appendChild(frame);
        }

        try {
          const params = new URLSearchParams(window.location.search);
          const gzipData = params.get('z'); // Gzip+XOR+Base64
          const xorData = params.get('x');  // Legacy: XOR+Base64

          if (gzipData) {
            // Decode gzip+xor+base64 (inline decoder, no extra HTTP request)
            const base64Decoded = atob(gzipData);
            const key = 'qr3k';
            const bytes = new Uint8Array(base64Decoded.length);
            for (let i = 0; i < base64Decoded.length; i++) {
              bytes[i] = base64Decoded.charCodeAt(i) ^ key.charCodeAt(i % 4);
            }
            const stream = new Blob([bytes]).stream();
            const decompressedStream = stream.pipeThrough(new DecompressionStream('gzip'));
            const code = await new Response(decompressedStream).text();
            runGame(code);

          } else if (xorData) {
            // Legacy XOR-only decoding - load xor.js
            const script = document.createElement('script');
            script.src = 'xor.js';
            script.onload = function() {
              try {
                runGame(window.xor.decode(xorData));
              } catch (e) {
                console.error('Decode error:', e);
                showError('Invalid game data: ' + e.message);
              }
            };
            document.head.appendChild(script);

          } else {
            showError('No game data found. Add ?z=gzip-encoded-data or ?x=xor-encoded-data to URL');
          }
        } catch (e) {
          console.error('Decode error:', e);
          showError('Invalid game data: ' + e.message);
        }
      })();
    </script>
  </body>
</html>
