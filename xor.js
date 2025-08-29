// XOR encryption/decryption using "qr3k" as the repeating key
// Designed to obfuscate JavaScript code for iOS Safari compatibility

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

function executeContent(content) {
  if (isHtmlContent(content)) {
    // Inject as HTML
    const container = document.getElementById('container');
    if (container) {
      container.innerHTML = content;
    } else {
      document.body.innerHTML = content;
    }
  } else {
    // Execute as JavaScript
    eval(content);
  }
}

// Export for Node.js if available, otherwise attach to window
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { encode, decode, isHtmlContent, executeContent };
} else if (typeof window !== 'undefined') {
  window.xor = { encode, decode, isHtmlContent, executeContent };
}