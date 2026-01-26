// Space Shooter - Shoot the invaders!
// Control: Arrow keys to move, Space to shoot | Goal: Survive and score high
// Size: ~1200 bytes

c=document.createElement('canvas')
c.width=400
c.height=500
document.body.appendChild(c)
x=c.getContext('2d')

// Player
px=180
// Bullets [{x,y}]
bl=[]
// Enemies [{x,y,vx}]
en=[]
// Score
sc=0
// Game over
g=0
// Keys pressed
k={}
// Spawn timer
t=0

addEventListener('keydown',e=>k[e.keyCode]=1)
addEventListener('keyup',e=>k[e.keyCode]=0)

setInterval(_=>{
  if(g)return

  t++

  // Move player
  if(k[37]&&px>0)px-=4
  if(k[39]&&px<380)px+=4

  // Shoot
  if(k[32]&&t%10==0)bl.push({x:px+10,y:470})

  // Spawn enemies
  if(t%60==0)en.push({x:~~(Math.random()*380),y:0,vx:Math.random()*2-1})

  // Update bullets
  for(i=bl.length-1;i>=0;i--){
    bl[i].y-=6
    if(bl[i].y<0)bl.splice(i,1)
  }

  // Update enemies
  for(i=en.length-1;i>=0;i--){
    en[i].y+=2
    en[i].x+=en[i].vx
    if(en[i].x<0||en[i].x>380)en[i].vx=-en[i].vx

    // Check collision with player
    if(en[i].y>460&&Math.abs(en[i].x-px)<20){
      g=1
      return
    }

    // Remove if off screen
    if(en[i].y>500)en.splice(i,1)
  }

  // Check bullet-enemy collision
  for(i=bl.length-1;i>=0;i--){
    for(j=en.length-1;j>=0;j--){
      if(Math.abs(bl[i].x-en[j].x)<15&&Math.abs(bl[i].y-en[j].y)<15){
        bl.splice(i,1)
        en.splice(j,1)
        sc++
        break
      }
    }
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,400,500)

  // Draw player
  x.fillStyle='#0f0'
  x.beginPath()
  x.moveTo(px+10,470)
  x.lineTo(px,490)
  x.lineTo(px+20,490)
  x.fill()

  // Draw bullets
  x.fillStyle='#ff0'
  for(i=0;i<bl.length;i++)x.fillRect(bl[i].x,bl[i].y,3,8)

  // Draw enemies
  x.fillStyle='#f00'
  for(i=0;i<en.length;i++){
    x.fillRect(en[i].x,en[i].y,20,15)
  }

  // Score
  x.fillStyle='#fff'
  x.fillText('Score: '+sc,10,20)

  if(g)x.fillText('GAME OVER',150,250)
},16)
