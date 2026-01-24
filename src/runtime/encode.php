<?php
session_start();
require_once __DIR__ . '/../encoding/php/Encoder.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QR3K Encoder</title>
    <link rel="stylesheet" href="style.css">
    <script src="xor.js"></script>
</head>
<body>
    <h1>QR3K Encoder</h1>
    <p>Paste your game code below (HTML or JavaScript) and generate a QR-compatible URL. New: Gzip compression saves 33-45%!</p>
    
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

        <div class="label" style="margin-top: 1.5rem;">Encoding Method:</div>
        <div class="method-selector">
            <label class="method-option">
                <input type="radio" name="method" value="gzip" checked>
                <span>Gzip+XOR (Recommended) - 33-45% smaller</span>
            </label>
            <label class="method-option">
                <input type="radio" name="method" value="xor">
                <span>XOR Only (Legacy) - Backward compatible</span>
            </label>
            <label class="method-option">
                <input type="radio" name="method" value="compare">
                <span>Compare Both - Show size difference</span>
            </label>
        </div>

        <br>
        <button type="button" id="encodeBtn" onclick="encodeGame()">Encode & Generate QR URL</button>
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

    <div id="comparison" class="output" style="display: none;">
        <div class="label">Size Comparison:</div>
        <div class="comparison-table">
            <div class="comparison-row">
                <div class="comparison-label">Raw Code:</div>
                <div class="comparison-value" id="rawSize"></div>
            </div>
            <div class="comparison-row">
                <div class="comparison-label">XOR Only (Legacy):</div>
                <div class="comparison-value" id="xorSize"></div>
            </div>
            <div class="comparison-row">
                <div class="comparison-label">Gzip+XOR (New):</div>
                <div class="comparison-value" id="gzipSize"></div>
            </div>
            <div class="comparison-row highlight">
                <div class="comparison-label">Bytes Saved:</div>
                <div class="comparison-value" id="bytesSaved"></div>
            </div>
            <div class="comparison-row highlight">
                <div class="comparison-label">Compression Ratio:</div>
                <div class="comparison-value" id="compressionRatio"></div>
            </div>
        </div>
        <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,215,0,0.1); border-left: 4px solid var(--yellow);">
            <strong>💡 Tip:</strong> The gzip method allows you to build games that are <span id="percentLarger"></span> larger while still fitting in the QR code!
        </div>
    </div>

    <div id="qrPreview" class="qr-section" style="display: none;">
        <div class="label">QR Code Preview:</div>
        <img id="qrImage" alt="QR Code">
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

        async function encodeGame() {
            const code = document.getElementById('codeInput').value;
            const button = document.getElementById('encodeBtn');
            const successMsg = document.getElementById('successMessage');
            const method = document.querySelector('input[name="method"]:checked').value;

            if (!code.trim()) {
                alert('Please enter some game code');
                return;
            }

            try {
                // Show loading state
                button.disabled = true;
                button.innerHTML = 'Encoding<span class="spinner"></span>';
                successMsg.style.display = 'none';

                // Hide previous results
                document.getElementById('output').style.display = 'none';
                document.getElementById('qrOutput').style.display = 'none';
                document.getElementById('qrPreview').style.display = 'none';
                document.getElementById('comparison').style.display = 'none';

                // Call API
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ code, method })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.error) {
                    throw new Error(result.error);
                }

                // Display results based on method
                if (method === 'compare') {
                    // Show comparison table
                    displayComparison(result);
                    document.getElementById('comparison').style.display = 'block';

                    // Show the gzip result as primary
                    document.getElementById('gameUrl').textContent = result.gzip.gameUrl;
                    document.getElementById('qrUrl').textContent = result.gzip.qrUrl;
                    document.getElementById('qrImage').src = result.gzip.qrUrl;
                } else {
                    // Show single result
                    document.getElementById('gameUrl').textContent = result.gameUrl;
                    document.getElementById('qrUrl').textContent = result.qrUrl;
                    document.getElementById('qrImage').src = result.qrUrl;
                }

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
                button.textContent = 'Encode & Generate QR URL';
            }
        }

        function displayComparison(result) {
            const rawSize = result.rawSize;
            const xorSize = result.xor.size.totalBytes;
            const gzipSize = result.gzip.size.totalBytes;
            const saved = xorSize - gzipSize;
            const ratio = result.gzip.size.compressionRatio;
            const percentLarger = Math.round((1 / ratio - 1) * 100);

            document.getElementById('rawSize').textContent = `${rawSize} bytes`;
            document.getElementById('xorSize').textContent = `${xorSize} bytes (${result.xor.size.base64Bytes} base64)`;
            document.getElementById('gzipSize').textContent = `${gzipSize} bytes (${result.gzip.size.base64Bytes} base64)`;
            document.getElementById('bytesSaved').innerHTML = `<strong style="color: var(--yellow)">${saved} bytes (${Math.round((saved / xorSize) * 100)}%)</strong>`;
            document.getElementById('compressionRatio').textContent = `${ratio.toFixed(2)}x smaller`;
            document.getElementById('percentLarger').innerHTML = `<strong style="color: var(--yellow)">${percentLarger}%</strong>`;
        }

        // Initialize counter on page load
        window.addEventListener('DOMContentLoaded', updateCounter);
    </script>
</body>
</html>