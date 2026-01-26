// Maze Runner - Navigate to the goal!
// Control: Arrow keys | Goal: Reach the green square
// Size: ~850 bytes

c=document.createElement('canvas')
c.width=400
c.height=400
document.body.appendChild(c)
x=c.getContext('2d')

// Player position
px=0
py=0
// Goal position
gx=19
gy=19
// Maze (20x20 grid, 1=wall, 0=path)
m=[]
for(i=0;i<400;i++)m[i]=Math.random()>.7?1:0
m[0]=0
m[399]=0
// Score (time)
t=0
// Win flag
w=0

addEventListener('keydown',e=>{
  k=e.keyCode
  ox=px
  oy=py
  if(k==37&&px>0)px--
  if(k==38&&py>0)py--
  if(k==39&&px<19)px++
  if(k==40&&py<19)py++
  // Check wall collision
  if(m[py*20+px]){
    px=ox
    py=oy
  }
  // Check win
  if(px==gx&&py==gy)w=1
})

setInterval(_=>{
  if(!w)t++

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,400)

  // Draw maze
  x.fillStyle='#444'
  for(i=0;i<400;i++){
    if(m[i])x.fillRect((i%20)*20,(~~(i/20))*20,20,20)
  }

  // Draw goal
  x.fillStyle='#0f0'
  x.fillRect(gx*20,gy*20,20,20)

  // Draw player
  x.fillStyle='#ff0'
  x.beginPath()
  x.arc(px*20+10,py*20+10,8,0,7)
  x.fill()

  // Draw grid
  x.strokeStyle='#111'
  for(i=0;i<21;i++){
    x.beginPath()
    x.moveTo(0,i*20)
    x.lineTo(400,i*20)
    x.stroke()
    x.beginPath()
    x.moveTo(i*20,0)
    x.lineTo(i*20,400)
    x.stroke()
  }

  // Time
  x.fillStyle='#fff'
  x.fillText('Time: '+(t/60|0)+'s',10,20)

  if(w)x.fillText('YOU WIN! Time: '+(t/60|0)+'s',120,200)
},16)
