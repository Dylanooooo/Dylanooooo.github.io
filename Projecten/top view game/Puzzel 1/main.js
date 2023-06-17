let character = document.querySelector(".character");
let map = document.querySelector(".map");

//start in the middle of the map
let x = 1355;
let y = 2480;
let held_directions = []; //State of which arrow keys we are holding down
let speed = 1; //How fast the character moves in pixels per frame
let hasKey = false;
let geluid = document.getElementById("audioId")

const placeCharacter = () => {
   
   let pixelSize = parseInt(
      getComputedStyle(document.documentElement).getPropertyValue('--pixel-size')
   );
   
   const held_direction = held_directions[0];
   if (held_direction) {
      if (held_direction === directions.right) {x += speed;}
      if (held_direction === directions.left) {x -= speed;}
      if (held_direction === directions.down) {y += speed;}
      if (held_direction === directions.up) {y -= speed;}
      character.setAttribute("facing", held_direction);
   }
   character.setAttribute("walking", held_direction ? "true" : "false");
   
   let camera_left = pixelSize * 66;
   let camera_top = pixelSize * 42;
   
   map.style.transform = `translate3d( ${-x*pixelSize+camera_left}px, ${-y*pixelSize+camera_top}px, 0 )`;
   character.style.transform = `translate3d( ${x*pixelSize}px, ${y*pixelSize}px, 0 )`;  
}


//Set up the game loop
const step = () => {
   placeCharacter();
   window.requestAnimationFrame(() => {
      step();
   })
}
step(); //kick off the first step!



/* Direction key state */
const directions = {
   up: "up",
   down: "down",
   left: "left",
   right: "right",
}
const keys = {
   38: directions.up,
   37: directions.left,
   39: directions.right,
   40: directions.down,
}
document.addEventListener("keydown", (e) => {
   let dir = keys[e.which];
   if (dir && held_directions.indexOf(dir) === -1) {
      held_directions.unshift(dir)
   }
})

document.addEventListener("keyup", (e) => {
   let dir = keys[e.which];
   let index = held_directions.indexOf(dir);
   if (index > -1) {
      held_directions.splice(index, 1)
   }
});

const collisionsMap = []
for (let i = 0; i < collisions.length; i += 250){
   collisionsMap.push(collisions.slice(i, 250 + i))
}

const canvas = document.querySelector('canvas')
const c = canvas.getContext('2d')

class boundary{
   static width = 12
   static height = 12
   constructor({position}){
      this.position = position
      this.width = 12
      this.height = 12
   }
   draw(){
      c.fillStyle = 'red'
      c.fillRect(this.position.x, this.position.y, this.width, this.height)
   }
}

const boundaries = []

collisionsMap.forEach((row, i) => {
   row.forEach((symbol, j) => {
      if (symbol === 2260)
      boundaries.push(new boundary({position:{
         x: j * boundary.width,
         y: i * boundary.height
      }}))
   })
})

console.log(boundaries)

function animate(){
   window.requestAnimationFrame(animate)
   boundaries.forEach(boundary => {
      boundary.draw()
   })
}
animate()

function keyF(){
   hasKey = true;
   console.log(hasKey);


}


function openDoor(){
   if (hasKey == true){
      setTimeout(gotoNext, 12000);
      alert("huh?") 
      document.getElementById("audioId").style.visibility = "visible"
      geluid.play();
   }
   else{
      alert("zoek een key")
   }
}
function gotoNext(){
   location.href = '/Puzzel 2/index.html';

}