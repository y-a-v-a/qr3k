// Stacker - Stack the falling blocks!
// Control: Arrow keys | Goal: Stack blocks without letting them pile to top
// Size: ~1000 bytes

c=document.createElement('canvas')
c.width=300
c.height=400
document.body.appendChild(c)
x=c.getContext('2d')

// Grid (10 cols, 20 rows)
g=[]
for(i=0;i<200;i++)g[i]=0

// Current block
bx=4
by=0
// Fall speed counter
fc=0
// Score (lines cleared)
sc=0
// Game over
go=0

addEventListener('keydown',e=>{
  k=e.keyCode
  if(k==37&&bx>0&&!g[by*10+bx-1])bx--
  if(k==39&&bx<9&&!g[by*10+bx+1])bx++
  if(k==40)fc=20 // Drop faster
})

setInterval(_=>{
  if(go)return

  fc++
  if(fc>20){
    fc=0
    // Try to move down
    if(by<19&&!g[(by+1)*10+bx]){
      by++
    }else{
      // Lock block
      g[by*10+bx]=1
      // Check if top row filled (game over)
      if(by==0){
        go=1
        return
      }
      // Check for complete rows
      for(r=19;r>=0;r--){
        f=1
        for(c=0;c<10;c++){
          if(!g[r*10+c])f=0
        }
        if(f){
          sc++
          // Remove row
          for(i=r*10;i>0;i--)g[i]=g[i-10]
          for(i=0;i<10;i++)g[i]=0
        }
      }
      // New block
      bx=~~(Math.random()*10)
      by=0
      if(g[bx]){
        go=1
        return
      }
    }
  }

  // Draw
  x.fillStyle='#000'
  x.fillRect(0,0,300,400)

  // Draw grid
  x.fillStyle='#0a0'
  for(i=0;i<200;i++){
    if(g[i])x.fillRect((i%10)*30,(~~(i/10))*20,28,18)
  }

  // Draw current block
  x.fillStyle='#0f0'
  x.fillRect(bx*30,by*20,28,18)

  // Grid lines
  x.strokeStyle='#222'
  for(i=0;i<21;i++)x.strokeRect(0,i*20,300,0)
  for(i=0;i<11;i++)x.strokeRect(i*30,0,0,400)

  // Score
  x.fillStyle='#fff'
  x.fillText('Lines: '+sc,5,15)

  if(go)x.fillText('GAME OVER',100,200)
},50)
