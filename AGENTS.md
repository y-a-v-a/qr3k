# AGENTS.md

## Project Overview
QR3K is a constraint-based game development challenge to create complete games that fit entirely within a QR code's data capacity (2,953 bytes max). Games are encoded as base64 HTML/JavaScript and decoded by a PHP runtime.

## Setup Commands
- Install dependencies: None required (pure HTML/JS/PHP project)
- Start dev server: `npm run dev` (uses Docker Compose)
- Start dev detached: `npm run dev:detached`
- View logs: `npm run logs`
- Shell into container: `npm run shell`
- Stop dev server: `npm run stop`
- Run tests: `npm test` (runs XOR encoding/decoding tests)
- Build for production: `npm run build:prod`
- Clean up Docker: `npm run clean`

## Development Environment
- **Runtime**: PHP 8.2 with Apache in Docker container
- **Local dev**: Docker Compose with volume mounting for live editing
- **Port**: 80 (mapped from Docker container)
- **Main files**: `src/runtime/index.php` (loader), `src/runtime/xor.js` (encoding utils)

## Code Style & Architecture
- **PHP**: Use sessions to bypass Varnish cache, minimal error handling
- **JavaScript**: Ultra-compressed for QR code constraints - single letter variables, no semicolons, mathematical functions preferred
- **HTML**: Single `<script>` tag when using HTML format
- **File naming**: Lowercase with dashes, keep file structure flat for simplicity

## Size Optimization Requirements
- **Target size**: Under 2,953 bytes after base64 encoding (~2,200 bytes raw content)
- **Variable names**: Single letters only (`c` for canvas, `x` for context, etc.)
- **Whitespace**: Remove all unnecessary whitespace
- **Canvas preferred**: More efficient than DOM manipulation
- **XOR encoding**: Use for iOS Safari compatibility (bypasses JavaScript filtering)

## Game Development Guidelines
- **Entry point**: Either pure JavaScript or HTML with single `<script>` tag
- **Canvas setup**: `const c=document.createElement('canvas');c.width=W;c.height=H;document.body.appendChild(c);`
- **Context**: `const x=c.getContext('2d');`
- **Game loop**: Use `requestAnimationFrame` for smooth animation
- **Input**: Keyboard events (`keydown`/`keyup`) or touch events for mobile
- **Recommended games**: Snake, Pong, Breakout, Tetris, Conway's Game of Life

## Encoding Process
1. Create game as HTML or JavaScript file
2. Run `npm run encode` to generate encoded URLs
3. Test both traditional and XOR-encoded versions
4. Generate QR code using: `https://cdn.vincentbruijn.nl/qr/img.php?q=<encoded-url>`
5. Test on multiple devices, especially iOS Safari

## File Structure
```
src/
├── runtime/           # PHP runtime and utilities
│   ├── index.php     # Main loader (handles both HTML and JS)
│   ├── xor.js        # XOR encoding/decoding utilities
│   └── encode.php    # PHP encoding helper
├── tools/
│   └── encode.js     # JavaScript encoding tool
└── games/
    ├── templates/    # Game templates for different patterns
    └── *.js         # Example games
```

## Testing
- **Unit tests**: `test/xor.test.js` - tests XOR encoding/decoding
- **Manual testing**: Create game → encode → test in browser
- **Cross-platform**: Test on desktop and mobile, especially iOS Safari
- **QR scanning**: Test actual QR code scanning on phones

## Deployment
- **Production build**: `npm run build:prod` creates optimized Docker image
- **Runtime URL**: `https://www.vincentbruijn.nl/qr3k/?x=<xor-encoded-content>`
- **Traditional URL**: `https://www.vincentbruijn.nl/qr3k/?g=<base64-encoded-content>`
- **QR generation**: `https://cdn.vincentbruijn.nl/qr/img.php?q=<full-url>`

## Common Issues
- **iOS Safari blocking**: Use XOR encoding (`?x=` parameter) instead of traditional base64 (`?g=`)
- **Size limits**: Monitor byte count carefully, base64 adds ~33% overhead  
- **Unicode handling**: XOR method works best with ASCII characters
- **Mobile performance**: Keep animations smooth on slower devices
- **URL length**: Some systems have URL length restrictions

## Development Tips
- Start with game templates in `src/games/templates/`
- Use mathematical functions for procedural generation
- Bit-pack game state where possible  
- Test frequently on target platforms
- Profile performance on mobile devices
- Consider touch controls for mobile compatibility