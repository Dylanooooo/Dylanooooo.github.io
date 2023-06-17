let first = true
let turn = ""
let clicks = 0

function start(){
    if(first == true){
        first = false 
        let randomStarter = Math.floor(Math.random()*2)
        if (randomStarter == 0){
            turn = "X"
            document.getElementById("showTurn").innerHTML = turn + "<br>" + "Turn";
        }
        else {
            turn = "O"
            document.getElementById("showTurn").innerHTML = turn + "<br>" + "Turn";
        }
    }
}

function change(event){
    if (event.target.textContent == "" && clickable == true && first == false){
        event.target.textContent = turn;
        if (turn == "X"){
            turn = "O"
            document.getElementById("showTurn").innerHTML = turn + "<br>" + "Turn";
            clicks += 1
        } else{
            turn = "X"
            document.getElementById("showTurn").innerHTML = turn + "<br>" + "Turn";
            clicks += 1
        }
        checkWinner()
    }
}

const tiles = document.querySelectorAll(".box")

for(let i = 0; i < tiles.length; i++){
    tiles[i].addEventListener("click", change);
}

let resetbutton = document.querySelector(".resetWinButton")
resetbutton.addEventListener("click", reset)

function reset(){
    let confirmation = confirm("Are you sure that you want to reset the win amounts?")
    if(confirmation == true){
        localStorage.clear()
        OwinAmount = 0
        XwinAmount = 0
        winAmount()
    }
}