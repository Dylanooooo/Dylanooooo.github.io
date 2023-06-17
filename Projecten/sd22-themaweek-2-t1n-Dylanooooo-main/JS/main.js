localStorage.removeItem("debug")

let username
let oof = new Audio("audio/oof.mp3")

let h1 = document.querySelector(".hole1")
let h2 = document.querySelector(".hole2")
let h3 = document.querySelector(".hole3")
let h4 = document.querySelector(".hole4")
let h5 = document.querySelector(".hole5")
let h6 = document.querySelector(".hole6")
let h7 = document.querySelector(".hole7")
let h8 = document.querySelector(".hole8")
let h9 = document.querySelector(".hole9")

let score = document.querySelector(".score")
let holes = [h1, h2, h3, h4, h5, h6, h7, h8, h9]

for (let i = 0; i < holes.length; i++) {
    holes[i].addEventListener("click", moleClick)
}

let minTime = 1000
let minTimeDecrease = setInterval(function(){
    minTime = minTime - 5
},1000)
let maxTime = 1000
let maxTimeDecrease = setInterval(function(){
    maxTime = maxTime - 5
},1000)

function generateRandomHole(){
    let randomNumber = Math.floor(Math.random()*holes.length)
    let randomHole = holes[randomNumber]
    randomHole.classList.toggle("moleIsHere")
    randomHole.innerHTML = '<img src="media/pngwing.com.png" class="mole">'
    let randomTime = Math.floor(Math.random() * maxTime + minTime)
    setTimeout(function(){
        if(randomHole.classList.contains("moleIsHere")){
            if(username == "Dylano"){
                randomHole.classList.toggle("moleIsHere")
                oof.currentTime = 0
                oof.play()
                score.innerHTML = +score.innerHTML + 1
            }else{
                randomHole.classList.toggle("moleIsHere")
                minTime = 1000
                maxTime = 1000
                if(+score.innerHTML > localStorage.getItem(username) || localStorage.getItem(username) === null){
                    localStorage.setItem(username, +score.innerHTML)
                    localStorage.removeItem("debug")
                    showLeaderBoard()
                }
                score.innerHTML = 0
            }
        }
        clear()
        generateRandomHole()
    }, randomTime)
}

function clear(){
    for (let i = 0; i < holes.length; i++) {
        holes[i].innerHTML = ""
    }
}

function moleClick(event){
    if(event.currentTarget.classList.contains("moleIsHere")){
        score.innerHTML = +score.innerHTML + 1
        event.currentTarget.classList.toggle("moleIsHere")
        oof.play()
        clear()
    } else{
        minTime = 1000
        maxTime = 1000
        if(+score.innerHTML > localStorage.getItem(username) || localStorage.getItem(username) === null){
            localStorage.setItem(username, +score.innerHTML)
            localStorage.removeItem("debug")
            showLeaderBoard()
        }
        score.innerHTML = 0
    }
}

document.querySelector(".clearLS").addEventListener("click", function(){
    localStorage.clear()
    showLeaderBoard()
})

let leaderBoard = document.querySelector(".leaderBoard")

function showLeaderBoard(){
    leaderBoard.innerHTML = ""
    for (let [key, value] of Object.entries(localStorage)) {
        leaderBoard.innerHTML += `<div class="leaderBoardItem"> ${key}: <div class="leaderBoardItemValue">${value}</div></div>`
    }
}
showLeaderBoard()

document.querySelector(".wrapper").style.filter = "blur(10px)"

let block = document.querySelector(".block")

let start = document.querySelector(".start")
start.addEventListener("click", function(){
    username = prompt("What is your name?", "Test")
    if(username.length > 15){
        alert("username is too long!")
    }else if(username == ""){
        alert("oof!")
    }else{
        document.querySelector(".wrapper").style.filter = "blur(0px)"
        start.style.display = "none"
        block.style.display = "none"
        generateRandomHole()
    }
})
