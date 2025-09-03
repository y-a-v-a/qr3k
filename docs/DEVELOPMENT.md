# QR3K Development Guide

This guide covers the development workflow, project structure, and best practices for working with the QR3K platform.

## Project Structure

```
qr3k/
├── src/
│   ├── runtime/           # Core platform files
│   │   ├── index.php      # Game loader (production)
│   │   └── xor.js         # XOR encoding/decoding utilities
│   ├── tools/             # Development tools
│   │   ├── encode.php     # Web-based encoder interface  
│   │   └── encode.js      # CLI encoder script
│   └── games/             # Example games and templates
│       ├── pong.js        # Example Pong game
│       └── templates/     # Game starter templates
├── test/                  # Testing files
│   └── xor.test.js        # XOR functionality tests
├── docs/                  # Documentation
│   ├── API.md             # XOR encoding API
│   ├── EXAMPLES.md        # Game examples and tutorials
│   └── DEVELOPMENT.md     # This file
├── package.json           # Node.js project configuration
├── .gitignore            # Git ignore patterns
└── README.md             # Main project documentation
```

## Development Workflow

### 1. Setup Environment

```bash
# Clone repository
git clone <repository-url>
cd qr3k

# Install dependencies (if any added)
npm install

# Run tests to verify setup
npm test
```

### 2. Create a New Game

#### Option A: Start from Template
```bash
# Copy a template as starting point
cp src/games/templates/canvas-game.js src/games/my-game.js

# Edit your game
nano src/games/my-game.js
```

#### Option B: Create from Scratch
```javascript
// src/games/my-game.js
c=document.createElement('canvas');
c.width=300;c.height=200;
document.body.appendChild(c);
x=c.getContext('2d');

// Your game logic here
setInterval(_=>{
  x.fillStyle='#000';
  x.fillRect(0,0,300,200);
  
  // Game rendering
  x.fillStyle='#0f0';
  x.fillRect(100,100,100,50);
},16);
```

### 3. Test Your Game Locally

#### Method A: Web Encoder Interface
```bash
# Start PHP server for web tools
cd src/tools
php -S localhost:8080

# Open http://localhost:8080/encode.php
# Paste your game code and test
```

#### Method B: CLI Encoding
```bash
# Generate encoded URLs
node src/tools/encode.js

# Or create custom encoding script
node -e "
const {encode} = require('./src/runtime/xor.js');
const fs = require('fs');
const game = fs.readFileSync('src/games/my-game.js', 'utf8');
const encoded = encode(game);
const url = \`https://www.vincentbruijn.nl/qr3k/?x=\${encodeURIComponent(encoded)}\`;
console.log('Game URL:', url);
console.log('Size:', Buffer.from(encoded, 'base64').length, 'bytes');
"
```

#### Method C: Local Runtime Testing  
```bash
# Start PHP server for runtime testing
cd src/runtime  
php -S localhost:8081

# Test with: http://localhost:8081/?x=<your-encoded-game>
```

### 4. Size Optimization

#### Check Current Size
```bash
# Quick size check
node -e "
const game = require('fs').readFileSync('src/games/my-game.js', 'utf8');
const {encode} = require('./src/runtime/xor.js');  
const encoded = encode(game);
const base64Size = encoded.length;
const binarySize = Buffer.from(encoded, 'base64').length;
console.log('Source:', game.length, 'bytes');
console.log('Encoded:', base64Size, 'bytes');  
console.log('Binary:', binarySize, 'bytes');
console.log('Under limit:', binarySize <= 2953 ? 'YES' : 'NO');
"
```

#### Optimization Techniques
See [EXAMPLES.md](EXAMPLES.md) for detailed optimization strategies.

### 5. Generate QR Code

```bash
# Generate QR code URL (using your encoded game URL)
GAME_URL="https://www.vincentbruijn.nl/qr3k/?x=<encoded-content>"
QR_URL="https://cdn.vincentbruijn.nl/qr/img.php?q=$(node -pe "encodeURIComponent('$GAME_URL')")"

echo "QR Code URL: $QR_URL"

# Test by opening in browser or using curl
curl -o my-game-qr.png "$QR_URL"
```

### 6. Test on Devices

1. **Desktop Browsers**: Test the game URL directly
2. **Mobile Browsers**: Generate QR code and scan
3. **iOS Safari**: Ensure XOR encoding works correctly
4. **Performance**: Test on slower mobile devices

## Development Scripts

### Package.json Scripts

```bash
# Run all tests
npm test

# Generate encoded URLs from example games  
npm run encode

# Start local PHP server for runtime
npm run dev

# Build/deployment preparation
npm run build
```

### Custom Scripts

#### Game Size Analyzer
```bash
# scripts/analyze-size.js
const fs = require('fs');
const { encode } = require('../src/runtime/xor.js');

const gameFile = process.argv[2];
if (!gameFile) {
  console.log('Usage: node scripts/analyze-size.js <game-file>');
  process.exit(1);
}

const game = fs.readFileSync(gameFile, 'utf8');
const encoded = encode(game);
const binarySize = Buffer.from(encoded, 'base64').length;

console.log(`Game: ${gameFile}`);
console.log(`Source size: ${game.length} bytes`);
console.log(`Encoded size: ${encoded.length} bytes (base64)`);
console.log(`Binary size: ${binarySize} bytes`);  
console.log(`Remaining: ${2953 - binarySize} bytes`);
console.log(`Under limit: ${binarySize <= 2953 ? 'YES' : 'NO'}`);

if (binarySize > 2953) {
  console.log(`OVER LIMIT BY: ${binarySize - 2953} bytes`);
  process.exit(1);
}
```

#### Batch Game Processing
```bash
# scripts/process-games.js
const fs = require('fs');
const path = require('path');
const { encode } = require('../src/runtime/xor.js');

const gamesDir = 'src/games';
const games = fs.readdirSync(gamesDir)
  .filter(f => f.endsWith('.js'))
  .map(f => path.join(gamesDir, f));

games.forEach(gameFile => {
  const game = fs.readFileSync(gameFile, 'utf8');
  const encoded = encode(game);
  const url = `https://www.vincentbruijn.nl/qr3k/?x=${encodeURIComponent(encoded)}`;
  const qrUrl = `https://cdn.vincentbruijn.nl/qr/img.php?q=${encodeURIComponent(url)}`;
  
  console.log(`\n${path.basename(gameFile)}:`);
  console.log(`Size: ${Buffer.from(encoded, 'base64').length} bytes`);
  console.log(`QR: ${qrUrl}`);
});
```

## Testing Strategy

### Automated Tests
```bash
# XOR encoding/decoding tests
npm test

# Add game-specific tests
# test/game-tests.js
const { encode, decode } = require('../src/runtime/xor.js');

// Test that games encode/decode properly
// Test that HTML games are detected correctly  
// Test size limits
```

### Manual Testing Checklist

- [ ] Game loads without errors
- [ ] Controls work on desktop
- [ ] Controls work on mobile (touch)
- [ ] Game fits within QR code size limit
- [ ] QR code scans correctly
- [ ] Game works on iOS Safari
- [ ] Performance acceptable on mobile
- [ ] Game logic functions correctly

### Device Testing Matrix

| Device | Browser | XOR Required | Status |
|--------|---------|--------------|--------|
| Desktop | Chrome | No | ✓ |
| Desktop | Firefox | No | ✓ |
| Desktop | Safari | No | ✓ |
| Android | Chrome | No | ✓ |
| Android | Firefox | No | ✓ |
| iOS | Safari | **Yes** | ⚠️ |
| iOS | Chrome | **Yes** | ⚠️ |

## Deployment

### Production Environment

The QR3K platform runs on:
- **Runtime**: PHP 7.4+ with session support  
- **Static Files**: Standard web server (nginx/apache)
- **Encoder Tools**: Can run on any PHP/Node.js environment

### Directory Structure in Production
```
/var/www/qr3k/
├── index.php          # From src/runtime/index.php
├── xor.js             # From src/runtime/xor.js  
└── tools/             # Optional: development tools
    └── encode.php     # From src/tools/encode.php
```

### Deployment Process
```bash
# Build deployment package
npm run build

# Copy files to production
rsync -av src/runtime/ user@server:/var/www/qr3k/
rsync -av src/tools/ user@server:/var/www/qr3k/tools/

# Update file permissions
ssh user@server 'chmod -R 644 /var/www/qr3k/*.php'
ssh user@server 'chmod -R 644 /var/www/qr3k/*.js'
```

## Troubleshooting

### Common Issues

#### "Invalid game data" Error
- Check that XOR encoding/decoding is working
- Verify base64 encoding is valid
- Test with minimal game first

#### Game Not Loading
- Check browser console for JavaScript errors
- Verify script paths are correct
- Test game code in isolation

#### iOS Safari Not Working  
- Ensure using `?x=` parameter (XOR encoded)
- Check that game doesn't use filtered keywords
- Test XOR encoding is properly obfuscating code

#### Size Limit Exceeded
- Use optimization techniques from EXAMPLES.md
- Remove unnecessary features
- Consider splitting into smaller games

### Debug Mode

```javascript
// Add to game code for debugging
console.log('Game loaded');
console.log('Canvas size:', c.width, 'x', c.height);
console.log('Context available:', !!x);

// Error handling wrapper  
try {
  // Game code here
} catch (e) {
  console.error('Game error:', e);
  document.body.innerHTML = '<h1>Game Error: ' + e.message + '</h1>';
}
```

### Performance Profiling

```javascript
// Add performance monitoring
let frameCount = 0;
let lastTime = Date.now();

setInterval(() => {
  const now = Date.now();
  const fps = frameCount / ((now - lastTime) / 1000);
  console.log('FPS:', fps.toFixed(1));
  frameCount = 0;
  lastTime = now;
}, 5000);

// In game loop
frameCount++;
```

## Contributing

### Code Style
- Use consistent minification style in games
- Include size comments in game files
- Test all changes with `npm test`
- Update documentation for new features

### Game Contributions
- Add games to `src/games/` directory
- Use descriptive filenames (`pong.js`, `snake.js`)
- Include size and feature comments
- Test on multiple devices

### Platform Contributions  
- Maintain PHP/JavaScript compatibility
- Update tests for new features
- Document API changes
- Preserve backward compatibility