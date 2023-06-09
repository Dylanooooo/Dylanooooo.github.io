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

// Moving background

let card = document.querySelector(".card")
let time = 0

setInterval(() => {
    let x = Math.floor(Math.random() * 100)
    let y = Math.floor(Math.random() * 100)
    card.style.backgroundPosition = `${x}% ${y}%`
    time = 5000
}, time)

// Remove text above image

let FotoOfMe = document.querySelector(".FotoOfMe")

FotoOfMe.addEventListener("mouseover", () => {
    document.querySelector(".me").style.opacity = "0"
})

FotoOfMe.addEventListener("mouseout", () => {
    document.querySelector(".me").style.opacity = "1"
})

// ----------------------------------------------------

let age = document.querySelector(".age")

setInterval(() => {
    let date = new Date()
    age.innerHTML = `<p>I am ${calcAge(date)}</p>`
}, 1);

function calcAge(date) {
    let currentAge = date.getTime() - 1159142400000
    // get my age in miliseconds

    let totalDays = currentAge/1000/60/60/24
    // get my age in days

    let yearsLeft = totalDays / 365.25
    let calcMonths = totalDays % 365.25
    
    let monthsLeft = calcMonths / 30.44
    let calcDays = calcMonths % 30.44
    
    let daysLeft = calcDays
    
    let totalAge = `${Math.floor(yearsLeft)} Years, ${Math.floor(monthsLeft)} Months and ${Math.floor(daysLeft)} Days old`
    return totalAge
}

// 1159142400000 is my birthdate