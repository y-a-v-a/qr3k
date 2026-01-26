// Snake - Classic Nokia game
// Control: Arrow keys | Goal: Eat food, don't hit walls or yourself
// Size: ~950 bytes

c=document.createElement('canvas')
c.width=300
c.height=300
document.body.appendChild(c)
x=c.getContext('2d')

// Snake segments [x,y]
s=[[15,15],[14,15],[13,15]]
// Direction: 0=right,1=down,2=left,3=up
d=0
// Food position
fx=~~(Math.random()*30)
fy=~~(Math.random()*30)
// Score
sc=0
// Game over flag
g=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37&&d!=0)d=2 // Left
  if(k==38&&d!=1)d=3 // Up
  if(k==39&&d!=2)d=0 // Right
  if(k==40&&d!=3)d=1 // Down
})

setInterval(_=>{
  if(g)return

  // Get head
  h=s[0]
  // Calculate new head position
  nx=h[0]+(d==0?1:d==2?-1:0)
  ny=h[1]+(d==1?1:d==3?-1:0)

  // Check wall collision
  if(nx<0||nx>29||ny<0||ny>29){
    g=1
    return
  }

  // Check self collision
  for(i=0;i<s.length;i++){
    if(s[i][0]==nx&&s[i][1]==ny){
      g=1
      return
    }
  }

  // Add new head
  s.unshift([nx,ny])

  // Check food
  if(nx==fx&&ny==fy){
    sc++
    fx=~~(Math.random()*30)
    fy=~~(Math.random()*30)
  }else{
    s.pop()
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,300,300)

  // Draw snake
  x.fillStyle='#0f0'
  for(i=0;i<s.length;i++){
    x.fillRect(s[i][0]*10,s[i][1]*10,9,9)
  }

  // Draw food
  x.fillStyle='#ff0'
  x.fillRect(fx*10,fy*10,9,9)

  // Draw score
  x.fillStyle='#fff'
  x.fillText('Score: '+sc,5,15)

  if(g){
    x.fillText('GAME OVER',100,150)
  }
},100)
