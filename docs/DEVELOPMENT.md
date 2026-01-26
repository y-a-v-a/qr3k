# QR3K Development Guide

This guide covers the development workflow, project structure, and best practices for working with the QR3K platform.

## Project Structure

```
qr3k/
├── .claude/               # Claude Code integration
│   └── skills/
│       └── qr3k-publisher/  # Skill for encoding/publishing games
│           ├── SKILL.md
│           └── scripts/   # Encode and QR fetch scripts
├── .github/               # GitHub Actions workflows
│   ├── workflows/
│   │   └── deploy.yml    # Automated deployment via SCP
│   └── DEPLOYMENT.md     # Deployment configuration guide
├── src/
│   ├── runtime/           # Core platform files
│   │   ├── index.php      # Game loader (production)
│   │   ├── encode.php     # Web-based encoder interface
│   │   ├── about.php      # About page with project info
│   │   ├── api.php        # JSON API for encoding
│   │   ├── xor.js         # XOR encoding/decoding utilities
│   │   └── style.css      # Centralized brutalist styling
│   ├── tools/             # Development tools
│   │   └── encode.js      # CLI encoder script
│   └── games/             # Example games and templates
│       ├── pong.js        # Example Pong game
│       └── templates/     # Game starter templates
├── test/                  # Testing files
│   └── xor.test.js        # XOR functionality tests
├── docs/                  # Documentation
│   ├── API.md             # XOR encoding API
│   ├── EXAMPLES.md        # Game examples and tutorials
│   ├── DOCKER.md          # Docker setup and usage (see below)
│   └── DEVELOPMENT.md     # This file
├── docker/                # Docker configuration
│   ├── Dockerfile         # Container definition
│   └── apache.conf        # Apache configuration
├── docker-compose.yml     # Docker Compose setup
├── package.json           # Node.js project configuration
├── .gitignore            # Git ignore patterns
└── README.md             # Main project documentation
```

## Development Environment

### Docker Setup (Recommended)

QR3K uses Docker for a consistent development environment. See **[DOCKER.md](DOCKER.md)** for complete Docker documentation.

**Quick Start:**
```bash
# Start development server with live reload
npm run dev

# Access at http://localhost:8080
# - Game loader: http://localhost:8080/
# - Web encoder: http://localhost:8080/encode.php
# - About page: http://localhost:8080/about.php
# - API endpoint: http://localhost:8080/api.php
```

**Available Commands:**
- `npm run dev` - Start dev server (foreground)
- `npm run dev:detached` - Start dev server (background)
- `npm run logs` - View container logs
- `npm run shell` - Access container shell
- `npm run stop` - Stop all containers
- `npm run build:prod` - Build production image

See [DOCKER.md](DOCKER.md) for full details on Docker commands, troubleshooting, and production deployment.

### Traditional Setup (Alternative)

```bash
# Clone repository
git clone <repository-url>
cd qr3k

# Install dependencies (if any added)
npm install

# Run tests to verify setup
npm test

# Start PHP server manually
cd src/runtime
php -S localhost:8080
```

## Development Workflow

### 1. Create a New Game

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

### 2. Test Your Game Locally

#### Method A: Web Encoder Interface
```bash
# If using Docker (recommended)
npm run dev
# Open http://localhost:8080/encode.php

# If using PHP built-in server
cd src/runtime
php -S localhost:8080
# Open http://localhost:8080/encode.php
```

The web encoder provides:
- Live character/byte counter with warnings
- Copy-to-clipboard for URLs
- Instant QR code preview
- Size validation

#### Method B: API Endpoint
```bash
# POST game code to API
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"code": "YOUR_GAME_CODE_HERE"}' | jq .

# Response includes gameUrl, qrUrl, and size info
```

#### Method C: Claude Code Skill
```bash
# Use the qr3k-publisher skill from Claude Code
# Automatically encodes, generates URLs, and fetches QR image
.claude/skills/qr3k-publisher/scripts/publish-game.sh src/games/my-game.js
```

#### Method D: CLI Encoding
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

### 3. Size Optimization

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

### 4. Generate QR Code

#### Using Web Encoder
The web encoder at `encode.php` automatically generates QR codes with preview.

#### Using API
```bash
# POST to API and get QR URL
RESPONSE=$(curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d "{\"code\": \"$(cat src/games/my-game.js)\"}" -s)

QR_URL=$(echo "$RESPONSE" | jq -r '.qrUrl')
curl -o my-game-qr.png "$QR_URL"
```

#### Using Claude Code Skill
```bash
# Complete workflow: encode + fetch QR
.claude/skills/qr3k-publisher/scripts/publish-game.sh src/games/my-game.js my-qr.png
```

### 5. Test on Devices

1. **Desktop Browsers**: Test the game URL directly
2. **Mobile Browsers**: Generate QR code and scan
3. **iOS Safari**: Ensure XOR encoding works correctly (use `?x=` parameter)
4. **Performance**: Test on slower mobile devices

## Claude Code Integration

### QR3K Publisher Skill

The repository includes a Claude Code skill for seamless game publishing:

**Location:** `.claude/skills/qr3k-publisher/`

**Usage from Claude Code:**
```
"Encode my game and generate a QR code"
"Create a QR code for snake.js"
"Publish my game"
```

**Direct Script Usage:**
```bash
# Complete workflow
.claude/skills/qr3k-publisher/scripts/publish-game.sh game.js

# Just encode (no QR download)
.claude/skills/qr3k-publisher/scripts/encode-game.sh game.js

# Just fetch QR
.claude/skills/qr3k-publisher/scripts/fetch-qr.sh "QR_URL" output.png
```

## API Endpoints

### POST /api.php

Encode game code and get URLs.

**Request:**
```json
{
  "code": "game code here"
}
```

**Response:**
```json
{
  "success": true,
  "gameUrl": "https://www.vincentbruijn.nl/qr3k/?x=...",
  "qrUrl": "https://cdn.vincentbruijn.nl/qr/img.php?q=...",
  "size": {
    "bytes": 1234,
    "base64Bytes": 1646,
    "limit": 2953,
    "isOverLimit": false
  }
}
```

See [API.md](API.md) for complete API documentation.

## Styling

All pages use centralized CSS in `src/runtime/style.css` with a brutalist design theme:

- **Color Palette**: Yellow (#FFD700), Magenta (#FF00FF), Blue (#00D4FF)
- **Design**: Bold blocky shadows, no rounded corners, high contrast
- **Responsive**: Mobile-first with breakpoints at 768px

**Pages:**
- `index.php` - Minimal game runtime (uses `body.game-runtime` class)
- `encode.php` - Full brutalist styling
- `about.php` - Brutalist styling with page-specific classes

## Deployment

### GitHub Actions (Automated)

The repository includes GitHub Actions for automated deployment via SCP.

**Setup:** See [.github/DEPLOYMENT.md](../.github/DEPLOYMENT.md)

**Required Secrets:**
- `SSH_PRIVATE_KEY` - SSH key for authentication
- `REMOTE_HOST` - Target server hostname
- `REMOTE_USER` - SSH username
- `REMOTE_PATH` - Destination directory

**Trigger:**
- Automatic on push to `main`
- Manual via Actions tab

### Manual Deployment

```bash
# Using Docker production image
npm run build:prod
npm run deploy
# Transfer qr3k-production.tar.gz to server

# Using SCP directly
scp -r src/runtime/* user@server:/var/www/qr3k/
```

See [DOCKER.md](DOCKER.md) for production deployment details.

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
- [ ] Game fits within QR code size limit (2,953 bytes)
- [ ] QR code scans correctly
- [ ] Game works on iOS Safari (XOR encoded)
- [ ] Performance acceptable on mobile
- [ ] Game logic functions correctly
- [ ] Styling consistent across pages

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

#### Docker Issues
See [DOCKER.md](DOCKER.md) for Docker-specific troubleshooting.

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
- Follow brutalist design principles for UI changes

### Game Contributions
- Add games to `src/games/` directory
- Use descriptive filenames (`pong.js`, `snake.js`)
- Include size and feature comments
- Test on multiple devices
- Ensure under 2,953 byte limit

### Platform Contributions
- Maintain PHP/JavaScript compatibility
- Update tests for new features
- Document API changes
- Preserve backward compatibility
- Test with Docker environment

## File Organization

### Runtime Files (`src/runtime/`)
- `index.php` - Game loader, minimal styling
- `encode.php` - Web encoder with full UI
- `about.php` - Project information page
- `api.php` - JSON API endpoint
- `xor.js` - Encoding utilities (shared client-side)
- `style.css` - Centralized styles for all pages

### Development Tools (`src/tools/`)
- `encode.js` - CLI encoding tool

### Claude Code (`.claude/`)
- Skills for automated workflows
- Shell scripts for common tasks

### Documentation (`docs/`)
- `API.md` - API reference
- `EXAMPLES.md` - Game examples and optimization
- `DOCKER.md` - **Docker setup and usage**
- `DEVELOPMENT.md` - This file

### Deployment (`.github/`)
- GitHub Actions workflows
- Deployment configuration guides
