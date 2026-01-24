// Pong - Keep the ball bouncing!
// Control: Arrow keys or mouse | Goal: Don't let ball pass your paddle
// Size: ~750 bytes

c=document.createElement('canvas')
c.width=400
c.height=300
document.body.appendChild(c)
x=c.getContext('2d')

// Paddle
px=160
pw=80
// Ball
bx=200
by=150
vx=3
vy=3
// Score (rally count)
sc=0
// Game over
g=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37&&px>0)px-=30
  if(k==39&&px<320)px+=30
})

addEventListener('mousemove',e=>{
  r=c.getBoundingClientRect()
  px=e.clientX-r.left-pw/2
  if(px<0)px=0
  if(px>400-pw)px=400-pw
})

setInterval(_=>{
  if(g)return

  // Update ball
  bx+=vx
  by+=vy

  // Wall bounce (left, right, top)
  if(bx<5||bx>395){vx=-vx;bx+=vx*2}
  if(by<5){vy=-vy;by=5}

  // Paddle bounce
  if(by>270&&by<280&&bx>px&&bx<px+pw){
    vy=-vy
    by=270
    sc++
    // Add spin
    vx+=(bx-(px+pw/2))/10
    // Speed up slightly
    if(sc%5==0){
      vx*=1.05
      vy*=1.05
    }
  }

  // Ball lost
  if(by>300){
    g=1
    return
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,300)

  // Draw center line
  x.fillStyle='#333'
  for(i=0;i<15;i++)x.fillRect(198,i*20,4,10)

  // Draw paddle
  x.fillStyle='#0f0'
  x.fillRect(px,280,pw,8)

  // Draw ball
  x.fillStyle='#fff'
  x.beginPath()
  x.arc(bx,by,6,0,7)
  x.fill()

  // Score
  x.fillText('Rally: '+sc,10,20)

  if(g)x.fillText('GAME OVER - Score: '+sc,120,150)
},16)
