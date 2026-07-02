// XOR obfuscation (NOT encryption — the 4-byte key is public) using "qr3k"
// as the repeating key. Its only job is to keep game code from being
// pattern-matched by iOS Safari's content filtering.

const XOR_KEY = "qr3k";

function xorWithKey(text, key) {
  const result = [];
  for (let i = 0; i < text.length; i++) {
    const textChar = text.charCodeAt(i);
    const keyChar = key.charCodeAt(i % key.length);
    result.push(String.fromCharCode(textChar ^ keyChar));
  }
  return result.join('');
}

function encode(text) {
  // XOR encrypt with "qr3k" key, then base64 encode
  const xorEncrypted = xorWithKey(text, XOR_KEY);
  return btoa(xorEncrypted);
}

function decode(encoded) {
  // Base64 decode, then XOR decrypt with "qr3k" key
  const base64Decoded = atob(encoded);
  return xorWithKey(base64Decoded, XOR_KEY);
}

function isHtmlContent(content) {
  // Check if content is HTML by looking for HTML indicators
  const trimmed = content.trim();
  return trimmed.startsWith('<') || 
         /<!DOCTYPE|<html|<head|<body|<script|<style|<div|<canvas/i.test(trimmed);
}

// Note: game execution lives in index.php (sandboxed iframe), not here.
// This file only provides the encode/decode primitives.

// Export for Node.js if available, otherwise attach to window
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { encode, decode, isHtmlContent };
} else if (typeof window !== 'undefined') {
  window.xor = { encode, decode, isHtmlContent };
}