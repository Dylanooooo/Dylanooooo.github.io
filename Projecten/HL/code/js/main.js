let imgarray = new Array();
let audio = new Audio("./clickSound/clickSound.mp3");
let Higher;
let guessed = false;
let m;
let n;
let punts = 100
const age = confirm(`Ben je 18 of ouder?`);
if (age == false){
    alert("je bent niet oud genoeg")
    window.close();
}

imgarray[0] = "Dice/Alea_1.png";
imgarray[1] = "Dice/Alea_2.png";
imgarray[2] = "Dice/Alea_3.png";
imgarray[3] = "Dice/Alea_4.png";
imgarray[4] = "Dice/Alea_5.png";
imgarray[5] = "Dice/Alea_6.png";

function higher(){
    Higher = true;
    guessed = true;
}

function lower(){
    Higher = false;
    guessed = true;
}

function rollDice1(){
    audio.play();
    let Image1 = document.getElementById("1");
    let Image2 = document.getElementById("2");
    let Die1 = Math.floor(Math.random()*6);
    let Die2 = Math.floor(Math.random()*6);
    m = Die1 + Die2
    console.log (Die1 + Die2 + 2);   
    Image1.src = imgarray[Die1];
    Image2.src = imgarray[Die2];
}

function rollDice2(){
    audio.play();
    let Image3 = document.getElementById("3");
    let Image4 = document.getElementById("4");
    let Die3 = Math.floor(Math.random()*6);
    let Die4 = Math.floor(Math.random()*6);
    let sliderInput = document.getElementById("sliderInput")
    let puntenAantal = document.getElementById("pointBox")
    n = Die3 + Die4
    console.log (Die3 + Die4 + 2);   
    Image3.src = imgarray[Die3];
    Image4.src = imgarray[Die4];
    if (m <= n && Higher){
        alert(`Goed`);
        punts += parseInt(sliderInput.value);
        rollDice1();
    }
    else if( m >= n && !Higher){
        alert(`Goed`);
        punts += parseInt(sliderInput.value);
        rollDice1();
    }
    else{
        alert("Fout");
        punts = punts - parseInt(sliderInput.value);
        rollDice1();
    }
    puntenAantal.textContent = punts;
    sliderInput.max = punts;
    if (punts == 0){
        alert("You Ded");
        document.getElementById("again").style.visibility = "visible";
    }
}

function guessButton(){
    if (guessed){
        if(Higher){
            rollDice2();
        }
        
        else{
            rollDice2();
        }
        guessed = false;
    }
    
    else{
        alert("Selecteer Higher of Lower!");
    }
}

function hideButton(x){
    x.style.display = `none`;
    document.querySelector(".container1").classList.add('no-blur');
    document.querySelector(".container2").classList.add('no-blur');
    document.querySelector(".container3").classList.add('no-blur');
    document.querySelector(".container6").classList.add('no-blur');
    document.querySelector(".container7").classList.add('no-blur');
    document.querySelector(".container8").classList.add('no-blur');
    document.getElementById("start").style.visibility = "hidden";
}

function goToRules(){
    document.getElementById("start").style.visibility = "hidden";
    document.getElementById("rules").style.visibility = "visible";
}

function terug(){
    document.getElementById("rules").style.visibility = "hidden";
    document.getElementById("start").style.visibility = "visible";
}