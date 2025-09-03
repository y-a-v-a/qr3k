// Interactive Game Template - Mouse/touch controls
// Size: ~350 bytes | Encoded: ~466 bytes | Remaining: ~2487 bytes

c=document.createElement('canvas');
c.width=400;
c.height=300;
document.body.appendChild(c);
x=c.getContext('2d');

// Game variables
px=200;py=150;mx=200;my=150;

// Mouse/touch handler
m=e=>{mx=e.x||e.touches[0].clientX;my=e.y||e.touches[0].clientY};
addEventListener('mousemove',m);
addEventListener('touchmove',m);

// Game loop
setInterval(_=>{
  // Clear screen
  x.fillStyle='#000';
  x.fillRect(0,0,400,300);
  
  // Move player toward mouse
  px+=(mx-px)*0.1;
  py+=(my-py)*0.1;
  
  // Draw player
  x.fillStyle='#0f0';
  x.fillRect(px-10,py-10,20,20);
  
  // Draw target
  x.fillStyle='#f00';
  x.fillRect(mx-5,my-5,10,10);
},16);