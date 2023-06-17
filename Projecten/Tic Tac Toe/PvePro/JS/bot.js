let tileArray = [t1, t2, t3, t4, t5, t6, t7, t8, t9]

function bot(){

    if(t1.textContent == "X" && t2.textContent == "X" && t3.textContent == ""){ // 1,2,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t4.textContent == "X" && t5.textContent == "X" && t6.textContent == ""){ // 4,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t6.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "X" && t8.textContent == "X" && t9.textContent == ""){ // 7,8,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "X" && t8.textContent == "X" && t7.textContent == ""){ // 9,8,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t6.textContent == "X" && t5.textContent == "X" && t4.textContent == ""){ // 6,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t4.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "X" && t2.textContent == "X" && t1.textContent == ""){ // 3,2,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "X" && t3.textContent == "X" && t2.textContent == ""){ // 1,0,3
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t2.textContent = "X"
        checkWinner()
    }else if(t4.textContent == "X" && t6.textContent == "X" && t5.textContent == ""){ // 4,0,6
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "X" && t9.textContent == "X" && t8.textContent == ""){ // 7,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t8.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "X" && t7.textContent == "X" && t4.textContent == ""){ // 1,0,7
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t4.textContent = "X"
        checkWinner()
    }else if(t2.textContent == "X" && t8.textContent == "X" && t5.textContent == ""){ // 2,0,8
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "X" && t9.textContent == "X" && t6.textContent == ""){ // 3,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t6.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "X" && t4.textContent == "X" && t7.textContent == ""){ // 1,4,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t2.textContent == "X" && t5.textContent == "X" && t8.textContent == ""){ // 2,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t8.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "X" && t6.textContent == "X" && t9.textContent == ""){ // 3,6,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "X" && t4.textContent == "X" && t1.textContent == ""){ // 7,4,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t8.textContent == "X" && t5.textContent == "X" && t2.textContent == ""){ // 8,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t2.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "X" && t6.textContent == "X" && t3.textContent == ""){ // 9,6,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "X" && t5.textContent == "X" && t9.textContent == ""){ // 1,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "X" && t5.textContent == "X" && t7.textContent == ""){ // 3,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "X" && t9.textContent == "X" && t5.textContent == ""){ // 1,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "X" && t7.textContent == "X" && t5.textContent == ""){ // 3,0,7
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "X" && t5.textContent == "X" && t3.textContent == ""){ // 7,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "X" && t5.textContent == "X" && t1.textContent == ""){ // 9,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "X" && t3.textContent == "X" && t5.textContent == ""){ // 7,0,3
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "X" && t1.textContent == "X" && t5.textContent == ""){ // 9,0,1
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }


    
    else if(t1.textContent == "O" && t2.textContent == "O" && t3.textContent == ""){ // 1,2,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t4.textContent == "O" && t5.textContent == "O" && t6.textContent == ""){ // 4,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t6.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "O" && t8.textContent == "O" && t9.textContent == ""){ // 7,8,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "O" && t8.textContent == "O" && t7.textContent == ""){ // 9,8,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t6.textContent == "O" && t5.textContent == "O" && t4.textContent == ""){ // 6,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t4.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "O" && t2.textContent == "O" && t1.textContent == ""){ // 3,2,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "O" && t3.textContent == "O" && t2.textContent == ""){ // 1,0,3
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t2.textContent = "X"
        checkWinner()
    }else if(t4.textContent == "O" && t6.textContent == "O" && t5.textContent == ""){ // 4,0,6
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "O" && t9.textContent == "O" && t8.textContent == ""){ // 7,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t8.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "O" && t7.textContent == "O" && t4.textContent == ""){ // 1,0,7
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t4.textContent = "X"
        checkWinner()
    }else if(t2.textContent == "O" && t8.textContent == "O" && t5.textContent == ""){ // 2,0,8
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "O" && t9.textContent == "O" && t6.textContent == ""){ // 3,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t6.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "O" && t4.textContent == "O" && t7.textContent == ""){ // 1,4,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t2.textContent == "O" && t5.textContent == "O" && t8.textContent == ""){ // 2,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t8.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "O" && t6.textContent == "O" && t9.textContent == ""){ // 3,6,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "O" && t4.textContent == "O" && t1.textContent == ""){ // 7,4,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t8.textContent == "O" && t5.textContent == "O" && t2.textContent == ""){ // 8,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t2.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "O" && t6.textContent == "O" && t3.textContent == ""){ // 9,6,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "O" && t5.textContent == "O" && t9.textContent == ""){ // 1,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t9.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "O" && t5.textContent == "O" && t7.textContent == ""){ // 3,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t7.textContent = "X"
        checkWinner()
    }else if(t1.textContent == "O" && t9.textContent == "O" && t5.textContent == ""){ // 1,0,9
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t3.textContent == "O" && t7.textContent == "O" && t5.textContent == ""){ // 3,0,7
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "O" && t5.textContent == "O" && t3.textContent == ""){ // 7,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t3.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "O" && t5.textContent == "O" && t1.textContent == ""){ // 9,5,0
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t1.textContent = "X"
        checkWinner()
    }else if(t7.textContent == "O" && t3.textContent == "O" && t5.textContent == ""){ // 7,0,3
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else if(t9.textContent == "O" && t1.textContent == "O" && t5.textContent == ""){ // 9,0,1
        clicks += 1
        document.getElementById("showTurn").innerHTML = "Player" + "<br>" + "Turn";
        Oturn = true
        t5.textContent = "X"
        checkWinner()
    }else{
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
}