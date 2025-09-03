<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QR3K Encoder</title>
    <style>
        body {
            font-family: monospace;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #000;
            color: #0f0;
        }
        textarea {
            width: 100%;
            height: 200px;
            background: #111;
            color: #0f0;
            border: 1px solid #333;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
        }
        button {
            background: #333;
            color: #0f0;
            border: 1px solid #555;
            padding: 10px 20px;
            cursor: pointer;
            font-family: monospace;
            margin-top: 10px;
        }
        button:hover {
            background: #555;
        }
        .output {
            margin-top: 20px;
            padding: 15px;
            background: #111;
            border: 1px solid #333;
            word-wrap: break-word;
        }
        .qr-section {
            margin-top: 20px;
            text-align: center;
        }
        h1 { color: #0f0; }
        .label { margin-top: 15px; margin-bottom: 5px; }
    </style>
    <script src="xor.js"></script>
</head>
<body>
    <h1>QR3K XOR Encoder</h1>
    <p>Paste your game code below (HTML or JavaScript) and generate a QR-compatible URL with XOR obfuscation for iOS Safari compatibility.</p>
    
    <form id="encodeForm">
        <div class="label">Game Code (HTML or JavaScript):</div>
        <textarea id="codeInput" placeholder="// JavaScript example:
const c = document.createElement('canvas');
c.width = 300; c.height = 200;
document.body.appendChild(c);
const ctx = c.getContext('2d');
ctx.fillStyle = '#0a0';
ctx.fillRect(50, 50, 200, 100);
ctx.fillText('Hello QR3K!', 80, 110);

// HTML example (single script tag only):
<canvas id='c' width='300' height='200'></canvas>
<script>
const ctx = document.getElementById('c').getContext('2d');
ctx.fillStyle = '#0a0';
ctx.fillRect(50, 50, 200, 100);
ctx.fillText('Hello QR3K!', 80, 110);
</script>"></textarea>
        <br>
        <button type="button" onclick="encodeGame()">XOR Encode & Generate QR URL</button>
    </form>

    <div id="output" class="output" style="display: none;">
        <div class="label">QR3K URL:</div>
        <div id="gameUrl"></div>
    </div>

    <div id="qrOutput" class="output" style="display: none;">
        <div class="label">QR Code Image URL:</div>
        <div id="qrUrl"></div>
    </div>

    <div id="qrPreview" class="qr-section" style="display: none;">
        <div class="label">QR Code Preview:</div>
        <img id="qrImage" alt="QR Code" style="border: 2px solid #333; background: white; padding: 10px;">
    </div>

    <script>
        function encodeGame() {
            const code = document.getElementById('codeInput').value;
            if (!code.trim()) {
                alert('Please enter some JavaScript code');
                return;
            }

            try {
                // Use the imported xor.js encode function
                const encoded = window.xor.encode(code);
                const urlSafe = encodeURIComponent(encoded);
                const gameUrl = `https://www.vincentbruijn.nl/qr3k/?x=${urlSafe}`;
                const qrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(gameUrl)}`;

                // Display results
                document.getElementById('gameUrl').textContent = gameUrl;
                document.getElementById('qrUrl').textContent = qrUrl;
                document.getElementById('qrImage').src = qrUrl;

                // Show output sections
                document.getElementById('output').style.display = 'block';
                document.getElementById('qrOutput').style.display = 'block';
                document.getElementById('qrPreview').style.display = 'block';

            } catch (error) {
                alert('Encoding error: ' + error.message);
            }
        }
    </script>
</body>
</html>