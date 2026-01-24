// Bounce - Keep the ball up with moving paddle!
// Control: Arrow keys | Goal: Achieve highest bounce count
// Size: ~850 bytes

c=document.createElement('canvas')
c.width=400
c.height=400
document.body.appendChild(c)
x=c.getContext('2d')

// Paddle (moves horizontally)
px=160
pv=3
pw=80
// Ball
bx=200
by=200
vx=0
vy=3
// Bounce count
bc=0
// High score
hs=0
// Game over
g=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37)pv=-4
  if(k==39)pv=4
})

addEventListener('keyup',e=>{
  k=e.keyCode
  if(k==37||k==39)pv=0
})

setInterval(_=>{
  if(g){
    // Press any key to restart
    addEventListener('keydown',_=>{
      bx=200
      by=200
      vx=0
      vy=3
      px=160
      bc=0
      g=0
    },{once:1})
    return
  }

  // Move paddle
  px+=pv
  if(px<0)px=0
  if(px>320)px=320

  // Update ball
  bx+=vx
  by+=vy
  vy+=.05 // Gravity

  // Wall bounce
  if(bx<5||bx>395){
    vx=-vx*.9
    bx+=vx*2
  }

  // Paddle bounce
  if(by>340&&by<360&&bx>px&&bx<px+pw){
    vy=-vy*1.05
    by=340
    bc++
    if(bc>hs)hs=bc
    // Transfer paddle momentum
    vx=pv*.5
  }

  // Ball lost
  if(by>400){
    g=1
    return
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,400)

  // Draw paddle
  x.fillStyle='#0f0'
  x.fillRect(px,350,pw,10)

  // Draw ball with trail
  x.fillStyle='rgba(255,255,0,.5)'
  x.beginPath()
  x.arc(bx,by,6,0,7)
  x.fill()

  // Scores
  x.fillStyle='#fff'
  x.fillText('Bounces: '+bc,10,20)
  x.fillText('Best: '+hs,10,35)

  if(g){
    x.fillText('GAME OVER',150,200)
    x.fillText('Press any key',140,220)
  }
},16)
