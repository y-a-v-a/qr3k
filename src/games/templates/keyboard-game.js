// Keyboard Game Template - Arrow key controls
// Size: ~400 bytes | Encoded: ~532 bytes | Remaining: ~2421 bytes

c=document.createElement('canvas');
c.width=400;
c.height=300;
document.body.appendChild(c);
x=c.getContext('2d');

// Game variables  
px=200;py=150;vx=0;vy=0;

// Keyboard handler
addEventListener('keydown',e=>{
k=e.keyCode;
if(k==37)vx=-2; // Left
if(k==38)vy=-2; // Up  
if(k==39)vx=2;  // Right
if(k==40)vy=2;  // Down
});

addEventListener('keyup',e=>{
k=e.keyCode;
if(k==37||k==39)vx=0;
if(k==38||k==40)vy=0;
});

// Game loop
setInterval(_=>{
  // Update position
  px+=vx;py+=vy;
  
  // Wrap around screen
  if(px<0)px=400;
  if(px>400)px=0;
  if(py<0)py=300;
  if(py>300)py=0;
  
  // Clear screen
  x.fillStyle='#000';
  x.fillRect(0,0,400,300);
  
  // Draw player
  x.fillStyle='#0f0';
  x.fillRect(px-10,py-10,20,20);
},16);