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
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        .counter {
            margin-top: 5px;
            font-size: 12px;
            color: #888;
        }
        .counter.warning {
            color: #ff0;
        }
        .counter.error {
            color: #f00;
        }
        .success-message {
            margin-top: 10px;
            padding: 10px;
            background: #1a3a1a;
            border: 1px solid #0f0;
            color: #0f0;
            border-radius: 3px;
            display: none;
        }
        .copy-btn {
            background: #444;
            color: #0f0;
            border: 1px solid #666;
            padding: 5px 10px;
            cursor: pointer;
            font-family: monospace;
            margin-left: 10px;
            font-size: 12px;
        }
        .copy-btn:hover {
            background: #666;
        }
        .copy-btn.copied {
            background: #1a3a1a;
            border-color: #0f0;
        }
        .url-container {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .url-text {
            flex: 1;
            min-width: 200px;
            word-break: break-all;
        }
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #333;
            border-top-color: #0f0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
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
</script>" oninput="updateCounter()"></textarea>
        <div id="charCounter" class="counter">0 bytes (0 bytes after base64) - Target: ~2,200 bytes (max 2,953 after encoding)</div>
        <br>
        <button type="button" id="encodeBtn" onclick="encodeGame()">XOR Encode & Generate QR URL</button>
        <div id="successMessage" class="success-message">✓ Successfully encoded! QR code generated.</div>
    </form>

    <div id="output" class="output" style="display: none;">
        <div class="label">QR3K URL:</div>
        <div class="url-container">
            <div id="gameUrl" class="url-text"></div>
            <button type="button" class="copy-btn" onclick="copyToClipboard('gameUrl', this)">Copy</button>
        </div>
    </div>

    <div id="qrOutput" class="output" style="display: none;">
        <div class="label">QR Code Image URL:</div>
        <div class="url-container">
            <div id="qrUrl" class="url-text"></div>
            <button type="button" class="copy-btn" onclick="copyToClipboard('qrUrl', this)">Copy</button>
        </div>
    </div>

    <div id="qrPreview" class="qr-section" style="display: none;">
        <div class="label">QR Code Preview:</div>
        <img id="qrImage" alt="QR Code" style="border: 2px solid #333; background: white; padding: 10px;">
    </div>

    <script>
        // Update character counter
        function updateCounter() {
            const code = document.getElementById('codeInput').value;
            const byteSize = new Blob([code]).size;
            const base64Size = Math.ceil(byteSize * 4 / 3);
            const counter = document.getElementById('charCounter');

            let counterClass = '';
            let message = `${byteSize} bytes (${base64Size} bytes after base64) - Target: ~2,200 bytes (max 2,953 after encoding)`;

            if (base64Size > 2953) {
                counterClass = 'error';
                message = `${byteSize} bytes (${base64Size} bytes after base64) - ⚠️ TOO LARGE! Exceeds QR code limit by ${base64Size - 2953} bytes`;
            } else if (base64Size > 2200) {
                counterClass = 'warning';
                message = `${byteSize} bytes (${base64Size} bytes after base64) - ⚠️ Warning: Approaching limit (${2953 - base64Size} bytes remaining)`;
            }

            counter.className = 'counter ' + counterClass;
            counter.textContent = message;
        }

        // Copy to clipboard function
        function copyToClipboard(elementId, button) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('copied');

                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        }

        function encodeGame() {
            const code = document.getElementById('codeInput').value;
            const button = document.getElementById('encodeBtn');
            const successMsg = document.getElementById('successMessage');

            if (!code.trim()) {
                alert('Please enter some game code');
                return;
            }

            // Check size before encoding
            const byteSize = new Blob([code]).size;
            const base64Size = Math.ceil(byteSize * 4 / 3);
            if (base64Size > 2953) {
                if (!confirm(`Warning: Your code is ${base64Size - 2953} bytes over the QR code limit. The QR code may not work. Continue anyway?`)) {
                    return;
                }
            }

            try {
                // Show loading state
                button.disabled = true;
                button.innerHTML = 'Encoding<span class="spinner"></span>';
                successMsg.style.display = 'none';

                // Small delay to show loading state
                setTimeout(() => {
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

                        // Show success message
                        successMsg.style.display = 'block';

                        // Scroll to results
                        document.getElementById('output').scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                    } catch (error) {
                        alert('Encoding error: ' + error.message);
                    } finally {
                        // Reset button state
                        button.disabled = false;
                        button.textContent = 'XOR Encode & Generate QR URL';
                    }
                }, 100);

            } catch (error) {
                alert('Encoding error: ' + error.message);
                button.disabled = false;
                button.textContent = 'XOR Encode & Generate QR URL';
            }
        }

        // Initialize counter on page load
        window.addEventListener('DOMContentLoaded', updateCounter);
    </script>
</body>
</html>