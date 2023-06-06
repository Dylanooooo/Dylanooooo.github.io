// Lightball effect behind cursor

const lb = document.createElement("div");

document.body.addEventListener("mousemove", function (e) {
    lb.className = "lightball";
    lb.style.left = e.clientX + "px";
    lb.style.top = e.clientY + "px";
    lb.style.position = "absolute";
    document.body.appendChild(lb);
})

// Word scramble effect

const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

let interval = null;

document.querySelector(".welcome").onmouseover = event => {
    let iteration = 0;

    clearInterval(interval);

    interval = setInterval(() => {
        event.target.innerText = event.target.innerText
            .split("")
            .map((letter, index) => {
                if (index < iteration) {
                    return event.target.dataset.value[index];
                }

                return letters[Math.floor(Math.random() * 36)]
            })
            .join("");

        if (iteration >= event.target.dataset.value.length) {
            clearInterval(interval);
        }

        iteration += 1 / 3;
    }, 30);
}

document.querySelector(".name").onmouseover = event => {
    let iteration = 0;

    clearInterval(interval);

    interval = setInterval(() => {
        event.target.innerText = event.target.innerText
            .split("")
            .map((letter, index) => {
                if (index < iteration) {
                    return event.target.dataset.value[index];
                }

                return letters[Math.floor(Math.random() * 36)]
            })
            .join("");

        if (iteration >= event.target.dataset.value.length) {
            clearInterval(interval);
        }

        iteration += 1 / 3;
    }, 30);
}

// -----------------------------------------------------------

let card = document.querySelector(".card")
let time = 0

setInterval(() => {
    let x = Math.floor(Math.random()*100)
    let y = Math.floor(Math.random()*100)
    card.style.backgroundPosition = `${x}% ${y}%`
    time = 5000
}, time)

// --------------------------------------------------------

let FotoOfMe = document.querySelector(".FotoOfMe")

FotoOfMe.addEventListener("mouseover", () => {
    document.querySelector(".me").style.opacity = "0"
})

FotoOfMe.addEventListener("mouseout", () => {
    document.querySelector(".me").style.opacity = "1"
})