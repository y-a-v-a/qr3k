// Breakout - Atari classic brick breaker
// Control: Arrow keys or mouse | Goal: Break all bricks
// Size: ~1100 bytes

c=document.createElement('canvas')
c.width=400
c.height=400
document.body.appendChild(c)
x=c.getContext('2d')

// Paddle
px=160
pw=80
// Ball
bx=200
by=350
vx=2
vy=-3
// Bricks (5 rows, 8 cols)
b=[]
for(i=0;i<40;i++)b[i]=1
// Score
sc=0
// Game state
w=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37&&px>0)px-=20
  if(k==39&&px<320)px+=20
})

addEventListener('mousemove',e=>{
  r=c.getBoundingClientRect()
  px=e.clientX-r.left-pw/2
  if(px<0)px=0
  if(px>400-pw)px=400-pw
})

setInterval(_=>{
  if(w)return

  // Update ball
  bx+=vx
  by+=vy

  // Wall bounce
  if(bx<5||bx>395){vx=-vx;bx+=vx*2}
  if(by<5){vy=-vy;by+=vy*2}

  // Paddle bounce
  if(by>370&&by<380&&bx>px&&bx<px+pw){
    vy=-vy
    by=370
    // Add spin based on hit position
    vx+=(bx-(px+pw/2))/10
  }

  // Ball lost
  if(by>400){
    w=1
    return
  }

  // Brick collision
  for(i=0;i<40;i++){
    if(!b[i])continue
    bxi=i%8
    byi=~~(i/8)
    bxx=bxi*50
    byy=byi*20+30
    if(bx>bxx&&bx<bxx+48&&by>byy&&by<byy+18){
      b[i]=0
      vy=-vy
      sc++
      if(sc==40)w=2
      break
    }
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,400)

  // Draw bricks
  for(i=0;i<40;i++){
    if(b[i]){
      h=['#f00','#fa0','#ff0','#0f0','#00f'][~~(i/8)]
      x.fillStyle=h
      x.fillRect((i%8)*50,(~~(i/8))*20+30,48,18)
    }
  }

  // Draw paddle
  x.fillStyle='#fff'
  x.fillRect(px,375,pw,10)

  // Draw ball
  x.beginPath()
  x.arc(bx,by,5,0,7)
  x.fill()

  // Score
  x.fillText('Score: '+sc,10,20)

  if(w==1)x.fillText('GAME OVER',160,200)
  if(w==2)x.fillText('YOU WIN!',170,200)
},16)
