let t1 = document.querySelector(".t1")
let t2 = document.querySelector(".t2")
let t3 = document.querySelector(".t3")
let t4 = document.querySelector(".t4")
let t5 = document.querySelector(".t5")
let t6 = document.querySelector(".t6")
let t7 = document.querySelector(".t7")
let t8 = document.querySelector(".t8")
let t9 = document.querySelector(".t9")

let Xwinner = false
let Owinner = false
let XwinAmount = Number(localStorage.getItem("Xwins"))
let OwinAmount = Number(localStorage.getItem("Owins"))
let clickable = true

function checkWinner(){
    if (t1.textContent == "X" && t2.textContent == "X" && t3.textContent == "X"){
        Xwinner = true
    } else if(t4.textContent == "X" && t5.textContent == "X" && t6.textContent == "X"){
        Xwinner = true
    } else if(t7.textContent == "X" && t8.textContent == "X" && t9.textContent == "X"){
        Xwinner = true
    } else if(t1.textContent == "X" && t4.textContent == "X" && t7.textContent == "X"){
        Xwinner = true
    } else if(t2.textContent == "X" && t5.textContent == "X" && t8.textContent == "X"){
        Xwinner = true
    } else if(t3.textContent == "X" && t6.textContent == "X" && t9.textContent == "X"){
        Xwinner = true
    } else if(t1.textContent == "X" && t5.textContent == "X" && t9.textContent == "X"){
        Xwinner = true
    } else if(t3.textContent == "X" && t5.textContent == "X" && t7.textContent == "X"){
        Xwinner = true
    }

    if (t1.textContent == "O" && t2.textContent == "O" && t3.textContent == "O"){
        Owinner = true
    } else if(t4.textContent == "O" && t5.textContent == "O" && t6.textContent == "O"){
        Owinner = true
    } else if(t7.textContent == "O" && t8.textContent == "O" && t9.textContent == "O"){
        Owinner = true
    } else if(t1.textContent == "O" && t4.textContent == "O" && t7.textContent == "O"){
        Owinner = true
    } else if(t2.textContent == "O" && t5.textContent == "O" && t8.textContent == "O"){
        Owinner = true
    } else if(t3.textContent == "O" && t6.textContent == "O" && t9.textContent == "O"){
        Owinner = true
    } else if(t1.textContent == "O" && t5.textContent == "O" && t9.textContent == "O"){
        Owinner = true
    } else if(t3.textContent == "O" && t5.textContent == "O" && t7.textContent == "O"){
        Owinner = true
    }

    if(clicks == 9 && Xwinner == false && Owinner == false){
        setTimeout(reload, 1000)
    }

    if (Xwinner == true){
        clickable = false
        setTimeout(function(){
            localStorage.setItem("Xwins", XwinAmount += 1)
            alert("X heeft gewonnen!"); reload();
        }, 500)
    } else if (Owinner == true){
        clickable = false
        setTimeout(function(){
            localStorage.setItem("Owins", OwinAmount += 1)
            alert("O heeft gewonnen!"); reload();
        }, 500)
    }
}

function reload(){
    window.location.reload()
}

let a = document.querySelector(".XwinAmount")
let b = document.querySelector(".OwinAmount")

function winAmount(){
    a.textContent = "X: " + localStorage.getItem("Xwins")
    b.textContent = "O: " + localStorage.getItem("Owins")
    if(localStorage.getItem("Xwins") === null){
        a.textContent = "X: 0"
    }
    if(localStorage.getItem("Owins") === null){
        b.textContent = "O: 0"
    }
}
winAmount()