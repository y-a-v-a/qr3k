// Snake 3310 - the Nokia classic: edges wrap around, Snake II style
// Control: Arrow keys | Goal: Eat food, grow, don't bite yourself
// Size: ~1,200 bytes

c=document.createElement('canvas')
c.width=308
c.height=336
document.body.appendChild(c)
x=c.getContext('2d')

// 28x28 grid of 11px cells below a 28px score bar
N=28

// Place food somewhere off the snake
function F(){
  do{fx=~~(Math.random()*N);fy=~~(Math.random()*N)}
  while(s.some(p=>p[0]==fx&&p[1]==fy))
}

// (Re)start
function R(){
  s=[[15,14],[14,14],[13,14]]
  // Direction: 0=right,1=down,2=left,3=up (q = queued, applied per tick)
  d=0
  q=0
  sc=0
  g=0
  F()
}
R()

addEventListener('keydown',e=>{
  e.preventDefault()
  if(g){R();return}
  k=e.keyCode
  if(k==37&&d!=0)q=2
  if(k==38&&d!=1)q=3
  if(k==39&&d!=2)q=0
  if(k==40&&d!=3)q=1
})

setInterval(_=>{
  // Nokia LCD: dark pixels on pea green
  x.fillStyle='#9ead86'
  x.fillRect(0,0,308,336)
  x.fillStyle='#2c3520'

  if(g){
    x.font='bold 24px monospace'
    x.fillText('GAME OVER',80,160)
    x.font='bold 14px monospace'
    x.fillText('Score: '+sc,120,190)
    x.fillText('Press any key',105,215)
    return
  }

  d=q
  h=s[0]
  // Wrap around the edges, Snake II style
  nx=(h[0]+(d==0?1:d==2?-1:0)+N)%N
  ny=(h[1]+(d==1?1:d==3?-1:0)+N)%N

  // Self collision ends the game
  if(s.some(p=>p[0]==nx&&p[1]==ny))g=1
  else{
    s.unshift([nx,ny])
    if(nx==fx&&ny==fy){sc++;F()}else s.pop()
  }

  // Score bar
  x.font='bold 16px monospace'
  x.fillText(sc,8,20)
  x.fillRect(0,26,308,2)

  // Snake: chunky segmented pixels
  for(i=0;i<s.length;i++)x.fillRect(s[i][0]*11+1,s[i][1]*11+29,9,9)

  // Food: hollow square
  x.strokeStyle='#2c3520'
  x.strokeRect(fx*11+2.5,fy*11+30.5,6,6)
},120)
