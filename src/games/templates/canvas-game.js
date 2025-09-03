// Canvas Game Template - Starting point for QR3K games
// Size: ~200 bytes | Encoded: ~266 bytes | Remaining: ~2687 bytes

c=document.createElement('canvas');
c.width=300;
c.height=200;
c.style.border='1px solid white';
document.body.appendChild(c);
x=c.getContext('2d');

// Game variables
t=0;

// Game loop
setInterval(_=>{
  // Clear screen
  x.fillStyle='#000';
  x.fillRect(0,0,300,200);
  
  // Game logic here
  // Example: animated square
  x.fillStyle='#0f0';
  x.fillRect(Math.sin(t/50)*100+125,Math.cos(t/30)*50+75,50,50);
  
  t++;
},16);