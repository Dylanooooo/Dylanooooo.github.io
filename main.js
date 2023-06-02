let navbar = document.querySelector(".navbar")
const lb = document.createElement("div");

document.body.addEventListener("mousemove", function(e){
    lb.className = "lightball";
    lb.style.left = e.clientX + "px";
    lb.style.top = e.clientY + "px";
    lb.style.position = "absolute";
    document.body.appendChild(lb);
})