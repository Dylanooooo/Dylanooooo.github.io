function bot(){
    let boxList = [t1, t2, t3, t4, t5, t6, t7, t8 ,t9]
    let botR = Math.floor(Math.random()*boxList.length)
    let botTurn = boxList[botR]
    if (botTurn.textContent == ""){
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        botTurn.textContent = "X"
        checkWinner()
    } else{
        bot()
    }
}