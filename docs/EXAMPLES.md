# QR3K Game Examples

This document provides practical examples and tutorials for creating QR3K games.

## Basic Canvas Game

### Minimal Template
```javascript
// Create canvas and setup
const c = document.createElement('canvas');
c.width = 300; c.height = 200;
c.style.border = '1px solid white';
document.body.appendChild(c);

const x = c.getContext('2d');

// Simple animation loop
function draw() {
  x.fillStyle = '#000';
  x.fillRect(0, 0, 300, 200);
  
  x.fillStyle = '#0f0';
  x.fillRect(50, 50, 200, 100);
  
  requestAnimationFrame(draw);
}
draw();
```

**Size**: ~280 bytes  
**Encoded Size**: ~373 bytes

## Pong Game Example

```javascript
c=document.createElement('canvas');c.width=innerWidth;c.height=innerHeight;c.style.position='fixed';c.style.top=0;c.style.left=0;document.body.appendChild(c);x=c.getContext('2d');w=c.width;h=c.height;p=w/2;b=[w/2,h/2,3,2];m=e=>p=e.x||e.touches[0].clientX;addEventListener('mousemove',m);addEventListener('touchmove',m);setInterval(_=>{x.fillStyle='#000';x.fillRect(0,0,w,h);x.fillStyle='#fff';x.fillRect(p-40,h-30,80,10);x.fillRect(b[0]-5,b[1]-5,10,10);b[0]+=b[2];b[1]+=b[3];if(b[0]<5||b[0]>w-5)b[2]=-b[2];if(b[1]<5)b[3]=-b[3];if(b[1]>h-40&&b[0]>p-45&&b[0]<p+45)b[3]=-b[3];if(b[1]>h)b=[w/2,h/2,3,2]},16)
```

**Features:**
- Mouse and touch control
- Ball physics with collision detection
- Paddle collision
- Ball reset on miss
- Fullscreen gameplay

**Size**: 602 bytes  
**Encoded Size**: ~802 bytes

## Snake Game Template

```javascript
// Ultra-compact Snake game
c=document.createElement('canvas');c.width=c.height=400;document.body.appendChild(c);x=c.getContext('2d');s=[[200,200]];d=[20,0];f=[100,100];setInterval(_=>{h=s[0];n=[h[0]+d[0],h[1]+d[1]];if(n[0]<0||n[0]>=400||n[1]<0||n[1]>=400||s.some(p=>p[0]==n[0]&&p[1]==n[1]))s=[[200,200]],d=[20,0],f=[100,100];else{s.unshift(n);if(n[0]==f[0]&&n[1]==f[1])f=[Math.floor(Math.random()*20)*20,Math.floor(Math.random()*20)*20];else s.pop()}x.fillStyle='#000';x.fillRect(0,0,400,400);x.fillStyle='#0f0';s.forEach(p=>x.fillRect(p[0],p[1],20,20));x.fillStyle='#f00';x.fillRect(f[0],f[1],20,20)},200);addEventListener('keydown',e=>{k=e.keyCode;if(k==37)d=[-20,0];if(k==38)d=[0,-20];if(k==39)d=[20,0];if(k==40)d=[0,20]})
```

**Features:**
- Arrow key controls
- Food collection and growth
- Collision detection
- Game reset on death

**Size**: ~650 bytes  
**Encoded Size**: ~866 bytes

## HTML Format Example

```html
<canvas id="c" width="300" height="200"></canvas>
<script>
x=document.getElementById('c').getContext('2d');
t=0;setInterval(_=>{
x.fillStyle='#000';x.fillRect(0,0,300,200);
x.fillStyle=`hsl(${t},50%,50%)`;
x.fillRect(Math.sin(t/50)*100+150,Math.cos(t/30)*50+100,20,20);
t++},16)
</script>
```

**Features:**
- HTML structure with single script tag
- Colorful animated bouncing ball
- Mathematical movement patterns

**Size**: ~250 bytes  
**Encoded Size**: ~333 bytes

## Game Development Tips

### Size Optimization Techniques

1. **Variable Names**: Use single letters (`c`, `x`, `w`, `h`)
2. **Remove Whitespace**: Eliminate all unnecessary spaces/newlines
3. **Omit Semicolons**: Skip them where JavaScript allows
4. **Combine Declarations**: Use comma operator (`a=1,b=2`)
5. **Use Template Literals**: For complex strings
6. **Arrow Functions**: Shorter than function declarations
7. **Ternary Operators**: Replace if/else when possible

### Before Optimization (150 bytes):
```javascript
function createCanvas() {
  const canvas = document.createElement('canvas');
  canvas.width = 300;
  canvas.height = 200;
  document.body.appendChild(canvas);
  return canvas.getContext('2d');
}
const context = createCanvas();
```

### After Optimization (65 bytes):
```javascript
c=document.createElement('canvas');c.width=300;c.height=200;document.body.appendChild(c);x=c.getContext('2d')
```

**Space Saved**: 85 bytes (57% reduction)

### Advanced Optimization

1. **Math Functions**: Use shortcuts
   - `Math.floor(Math.random()*n)` → `~~(Math.random()*n)`
   - `Math.abs(x)` → `x<0?-x:x`

2. **Array Shortcuts**:
   - `array.push(item)` → `array[array.length]=item`
   - `array.length=0` to clear array

3. **Canvas Shortcuts**:
   - Store frequently used values
   - Combine similar drawing operations

### Control Schemes

#### Keyboard Controls
```javascript
addEventListener('keydown',e=>{
k=e.keyCode;
if(k==37)moveLeft();
if(k==39)moveRight();
if(k==32)fire();
})
```

#### Mouse/Touch Controls  
```javascript
m=e=>mouseX=e.x||e.touches[0].clientX;
addEventListener('mousemove',m);
addEventListener('touchmove',m);
```

#### Mobile-Friendly Pattern
```javascript
// Combine mouse and touch in one handler
c.onmousedown=c.ontouchstart=e=>{
x=(e.touches?e.touches[0]:e).clientX;
// Handle input at x coordinate
}
```

## Game Ideas by Size Budget

### Tiny Games (< 500 bytes)
- **Dot Collector**: Move cursor to collect dots
- **Color Changer**: Click to cycle through colors  
- **Simple Animation**: Bouncing ball or rotating shape
- **Click Counter**: Basic interaction tracking

### Small Games (500-1000 bytes)
- **Basic Pong**: Single paddle, bouncing ball
- **Simplified Snake**: No growth, just movement
- **Asteroids-lite**: Rotating ship, basic shooting
- **Memory Pattern**: Simon-says style game

### Medium Games (1000-2000 bytes)  
- **Full Pong**: Two paddles, scoring
- **Complete Snake**: Growth, food, collision
- **Basic Breakout**: Paddle, ball, destructible blocks
- **Space Shooter**: Enemies, bullets, basic AI

### Large Games (2000+ bytes)
- **Advanced Tetris**: Piece rotation, line clearing
- **Multi-level Platformer**: Jump physics, obstacles  
- **Tower Defense**: Path-finding, multiple enemies
- **RPG Battle**: Turn-based combat, stats

## Testing Your Games

1. **Local Testing**: Use `npm run dev` to test with PHP server
2. **Encoding Test**: Run `npm run encode` to generate URLs  
3. **QR Testing**: Generate QR codes and test scanning
4. **Device Testing**: Test on iOS Safari specifically
5. **Size Verification**: Check final encoded size < 2953 bytes

## Common Pitfalls

### Size Overruns
- Always test encoded size, not source size
- Account for 33% base64 overhead
- Test with actual QR code generators

### iOS Safari Issues  
- Use XOR encoding (`?x=` parameter)
- Avoid obvious JavaScript keywords in source
- Test on actual iOS devices

### Performance Problems
- Limit animation complexity on mobile
- Use `requestAnimationFrame` for smooth animation
- Optimize canvas drawing operations

### Control Issues
- Test both mouse and touch input
- Handle screen size variations
- Consider landscape/portrait orientations