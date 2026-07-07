// Lunar Lander - touch down gently on the green pad
// Control: Up/Space = thrust, Left/Right = side thrusters | Watch fuel & speed
// Size: ~1,900 bytes

c=document.createElement('canvas')
c.width=400
c.height=400
document.body.appendChild(c)
x=c.getContext('2d')

K={}

// (Re)start: fresh terrain, fresh drop
function R(){
  t=[]
  for(i=0;i<=20;i++)t[i]=300+Math.sin(i*2.7)*25+Math.random()*50
  // Flatten two segments into a 40px landing pad
  p=2+~~(Math.random()*15)
  t[p+1]=t[p+2]=t[p]
  X=200
  Y=40
  vx=(Math.random()-.5)*2
  vy=0
  f=100
  th=0
  g=0
}
R()

addEventListener('keydown',e=>{
  e.preventDefault()
  if(g){if(!e.repeat)R();return}
  K[e.keyCode]=1
})
addEventListener('keyup',e=>{K[e.keyCode]=0})

setInterval(_=>{
  if(!g){
    // Thrust burns fuel, gravity is free
    th=(K[38]||K[32])&&f>0
    if(th){vy-=.09;f-=.4}
    if(K[37]&&f>0){vx-=.05;f-=.15}
    if(K[39]&&f>0){vx+=.05;f-=.15}
    vy+=.045
    X+=vx
    Y+=vy
    if(X<6){X=6;vx=0}
    if(X>394){X=394;vx=0}

    // Terrain height under the lander (linear interpolation)
    i=~~(X/20)
    gy=t[i]+(t[i+1]-t[i])*(X%20)/20
    if(Y+8>=gy){
      Y=gy-8
      // Gentle, level, and on the pad?
      g=(X-6>=p*20&&X+6<=p*20+40&&vy<1.4&&Math.abs(vx)<.8)?2:1
      th=0
    }
  }

  // Sky and stars
  x.fillStyle='#000'
  x.fillRect(0,0,400,400)
  x.fillStyle='#fff'
  for(i=0;i<40;i++)x.fillRect((i*97)%400,(i*i*3)%260,1,1)

  // Terrain
  x.beginPath()
  x.moveTo(0,400)
  for(i=0;i<=20;i++)x.lineTo(i*20,t[i])
  x.lineTo(400,400)
  x.closePath()
  x.fillStyle='#222'
  x.fill()
  x.strokeStyle='#888'
  x.stroke()

  // Landing pad
  x.fillStyle='#0f0'
  x.fillRect(p*20,t[p]-2,40,3)

  // Lander
  x.save()
  x.translate(X,Y)
  if(th){
    x.fillStyle='#fa0'
    x.beginPath()
    x.moveTo(-3,8)
    x.lineTo(0,15+Math.random()*5)
    x.lineTo(3,8)
    x.fill()
  }
  x.fillStyle=g==1?'#f00':'#ccc'
  x.fillRect(-6,-6,12,10)
  x.fillRect(-8,4,3,4)
  x.fillRect(5,4,3,4)
  x.restore()

  // HUD: fuel bar and velocity, green when safe to land
  x.font='12px monospace'
  x.fillStyle='#fff'
  x.fillText('FUEL',10,21)
  x.strokeStyle='#fff'
  x.strokeRect(48.5,12.5,101,9)
  x.fillStyle=f>25?'#0f0':'#f00'
  x.fillRect(49,13,Math.max(0,f),8)
  x.fillStyle=vy<1.4?'#0f0':'#f00'
  x.fillText('VY '+vy.toFixed(1),10,40)
  x.fillStyle=Math.abs(vx)<.8?'#0f0':'#f00'
  x.fillText('VX '+vx.toFixed(1),90,40)

  if(g){
    x.font='bold 24px monospace'
    if(g==2){
      x.fillStyle='#0f0'
      x.fillText('PERFECT LANDING!',85,180)
      x.font='bold 14px monospace'
      x.fillText('Fuel bonus: '+~~f,145,210)
    }else{
      x.fillStyle='#f00'
      x.fillText('CRASHED',150,180)
    }
    x.fillStyle='#fff'
    x.font='12px monospace'
    x.fillText('Press any key to fly again',120,240)
  }
},16)
