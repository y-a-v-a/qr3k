// Dodge - Avoid the falling blocks!
// Control: Arrow keys or mouse | Goal: Survive as long as possible
// Size: ~800 bytes

c=document.createElement('canvas')
c.width=400
c.height=500
document.body.appendChild(c)
x=c.getContext('2d')

// Player
px=180
// Obstacles [{x,y,w}]
ob=[]
// Score (survival time)
sc=0
// Game over
g=0
// Spawn timer
t=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37&&px>0)px-=20
  if(k==39&&px<360)px+=20
})

addEventListener('mousemove',e=>{
  r=c.getBoundingClientRect()
  px=e.clientX-r.left-20
  if(px<0)px=0
  if(px>360)px=360
})

setInterval(_=>{
  if(g)return

  t++
  sc++

  // Spawn obstacles
  if(t%(60-Math.min(sc/100|0,40))==0){
    ob.push({
      x:~~(Math.random()*360),
      y:0,
      w:20+~~(Math.random()*40)
    })
  }

  // Update obstacles
  for(i=ob.length-1;i>=0;i--){
    ob[i].y+=3+sc/500
    // Check collision
    if(ob[i].y>450&&ob[i].y<490&&
       px+40>ob[i].x&&px<ob[i].x+ob[i].w){
      g=1
      return
    }
    // Remove if off screen
    if(ob[i].y>500)ob.splice(i,1)
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,500)

  // Draw player
  x.fillStyle='#0f0'
  x.fillRect(px,460,40,30)

  // Draw obstacles
  x.fillStyle='#f00'
  for(i=0;i<ob.length;i++){
    x.fillRect(ob[i].x,ob[i].y,ob[i].w,20)
  }

  // Score
  x.fillStyle='#fff'
  x.fillText('Time: '+(sc/60|0)+'s',10,20)

  if(g)x.fillText('GAME OVER - Time: '+(sc/60|0)+'s',100,250)
},16)
