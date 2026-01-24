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
      No game data found. Add ?x=xor-encoded-data to URL
    </div>

    <script src="xor.js"></script>
    <script>
      try {
        // Extract game parameter from URL
        const params = new URLSearchParams(window.location.search);
        const xorData = params.get('x');

        if (xorData) {
          // URL decode first, then XOR decode and auto-detect content type
          const decoded = window.xor.decode(decodeURIComponent(xorData));
          window.xor.executeContent(decoded);
        } else {
          // Show error if no game data
          document.getElementById('error').style.display = 'block';
        }
      } catch (e) {
        // Handle decoding errors
        document.getElementById('error').innerHTML =
          'Invalid game data: ' + e.message;
        document.getElementById('error').style.display = 'block';
      }
    </script>
  </body>
</html>
