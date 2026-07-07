// Wall Pong - squash against the left wall
// Control: Up/Down arrows, mouse or touch | Goal: Keep the rally going
// Size: ~1,100 bytes

c=document.createElement('canvas')
c.width=400
c.height=300
document.body.appendChild(c)
x=c.getContext('2d')

// Paddle height
ph=60

// (Re)start
function R(){
  py=120
  bx=200
  by=150
  vx=3
  vy=2
  sc=0
  g=0
}
R()

addEventListener('keydown',e=>{
  e.preventDefault()
  if(g){R();return}
  k=e.keyCode
  if(k==38)py-=25
  if(k==40)py+=25
  py=Math.max(0,Math.min(240,py))
})

addEventListener('mousemove',e=>{
  r=c.getBoundingClientRect()
  py=Math.max(0,Math.min(240,e.clientY-r.top-ph/2))
})

addEventListener('touchmove',e=>{
  r=c.getBoundingClientRect()
  py=Math.max(0,Math.min(240,e.touches[0].clientY-r.top-ph/2))
})

setInterval(_=>{
  if(!g){
    bx+=vx
    by+=vy

    // Top/bottom bounce
    if(by<6){vy=-vy;by=6}
    if(by>294){vy=-vy;by=294}

    // The wall never misses
    if(bx<16){vx=-vx;bx=16}

    // Paddle bounce: add spin, speed up, count the rally
    if(vx>0&&bx>378&&bx<392&&by>py-6&&by<py+ph+6){
      vx=-vx*1.04
      if(vx<-9)vx=-9
      bx=378
      sc++
      vy+=(by-(py+ph/2))/12
      if(vy>5)vy=5
      if(vy<-5)vy=-5
    }

    // Ball lost
    if(bx>406)g=1
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,300)

  // Wall
  x.fillStyle='#666'
  x.fillRect(0,0,10,300)
  x.fillStyle='#444'
  for(i=0;i<10;i++)x.fillRect(0,i*30,10,2)

  // Paddle
  x.fillStyle='#0f0'
  x.fillRect(384,py,8,ph)

  // Ball
  x.fillStyle='#fff'
  x.beginPath()
  x.arc(bx,by,6,0,7)
  x.fill()

  // Score
  x.font='12px monospace'
  x.fillText('Rally: '+sc,180,20)

  if(g){
    x.font='bold 20px monospace'
    x.fillText('GAME OVER - Rally: '+sc,75,150)
    x.font='12px monospace'
    x.fillText('Press any key to restart',130,180)
  }
},16)
