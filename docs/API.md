# QR3K XOR Encoding API

## Overview

The QR3K platform uses XOR encoding to obfuscate JavaScript code, making it compatible with iOS Safari's filtering mechanisms. This document details the encoding API and functions available.

## Core Functions

### `encode(text)`

Encodes a string using XOR encryption followed by base64 encoding.

**Parameters:**
- `text` (string): The content to encode (HTML or JavaScript)

**Returns:**
- `string`: Base64-encoded XOR-encrypted content

**Example:**
```javascript
const { encode } = require('./xor.js');
const game = 'const c = document.createElement("canvas");';
const encoded = encode(game);
// Returns: base64 string safe for URL parameters
```

### `decode(encoded)`

Decodes a base64-encoded XOR-encrypted string back to original content.

**Parameters:**
- `encoded` (string): Base64-encoded XOR-encrypted content

**Returns:**
- `string`: Original content

**Example:**
```javascript
const { decode } = require('./xor.js');
const original = decode(encodedString);
```

### `isHtmlContent(content)`

Automatically detects whether content is HTML or JavaScript.

**Parameters:**
- `content` (string): Content to analyze

**Returns:**
- `boolean`: `true` if content appears to be HTML, `false` for JavaScript

**Detection Rules:**
- Starts with `<` character
- Contains HTML tags: `<!DOCTYPE`, `<html`, `<head`, `<body`, `<script`, `<style`, `<div`, `<canvas`
- Case-insensitive matching

**Example:**
```javascript
const { isHtmlContent } = require('./xor.js');

isHtmlContent('<canvas></canvas>'); // true
isHtmlContent('const x = 42;');     // false
```

### `executeContent(content)`

Executes content based on its detected type (HTML or JavaScript).

**Parameters:**
- `content` (string): Decoded content to execute

**Behavior:**
- **HTML Content**: Injects into DOM container, manually executes `<script>` tags
- **JavaScript Content**: Executes directly using `eval()`

**Example:**
```javascript
const { executeContent } = require('./xor.js');
executeContent('<canvas id="c"></canvas><script>/* game code */</script>');
```

## XOR Encryption Details

### Key
- **Key String**: `"qr3k"` 
- **Key Bytes**: `[113, 114, 51, 107]` (q, r, 3, k)

### Process
1. **Encryption**: Each byte of input is XOR'd with cycling key bytes
2. **Encoding**: Result is base64 encoded for URL safety
3. **Decoding**: Reverse process (base64 decode → XOR decrypt)

### Why XOR?
- **iOS Compatibility**: Obfuscates JavaScript keywords that trigger Safari filtering
- **Reversible**: Perfect reconstruction of original content
- **Lightweight**: Minimal overhead for QR code size constraints
- **URL Safe**: Base64 encoding ensures URL compatibility

## URL Parameter Format

```
https://www.vincentbruijn.nl/qr3k/?x=<encoded-content>
```

Where `<encoded-content>` is the URL-encoded result of `encode(gameCode)`.

## Size Considerations

- **Base64 Overhead**: ~33% size increase from encoding
- **XOR Overhead**: No size increase (1:1 byte mapping)
- **Total Overhead**: ~33% (base64 only)
- **QR Code Limit**: 2,953 bytes for Version 40 QR code
- **Effective Game Size**: ~2,200 bytes after encoding overhead

## Error Handling

### Common Errors
- **Invalid Base64**: Malformed encoded parameter
- **Unicode Issues**: `btoa`/`atob` limitations with non-ASCII characters
- **Script Execution**: JavaScript runtime errors in game code

### Best Practices
- Test encoded/decoded content before QR code generation
- Use ASCII characters when possible
- Handle execution errors gracefully in game code
- Validate QR code URL length limits

## Browser Compatibility

### Supported
- **Chrome/Edge**: Full support
- **Firefox**: Full support
- **Safari (macOS)**: Full support
- **Safari (iOS)**: Requires XOR encoding (`?x=` parameter)

### Not Supported
- Internet Explorer (lacks `btoa`/`atob`)
- Very old mobile browsers

## Development Workflow

1. **Create Game**: Write minimal HTML/JavaScript
2. **Encode**: Use `encode(gameCode)` 
3. **Generate URL**: Create QR3K URL with encoded parameter
4. **Create QR**: Generate QR code with full URL
5. **Test**: Scan QR code on target devices

## Security Notes

- XOR encoding is **obfuscation**, not cryptographic security
- Game code is fully recoverable by anyone
- Designed to bypass filtering, not provide security
- Suitable for public games and demos only