let navbar = document.querySelector(".navbar")
navbar.innerHTML += `<div class="lightball"></div>`
let lightball = document.querySelector(".lightball")
const lb = document.createElement("div");

navbar.addEventListener("mousemove", function(e){
    lb.className = "lightball";
    lb.style.left = e.clientX + "px";
    lb.style.top = e.clientY + "px";
    lb.style.position = "absolute";
    navbar.appendChild(lb);

    lightball.style.top = e.clientY
    lightball.style.left = e.clientX
})