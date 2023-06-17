let names = ["Nanda Kip", "Moam", "Shibrompad", "Aldi Malfi", "Maupie", "boosheid"]
let randomNumber = Math.floor(Math.random()*names.length)
let randomName = names[randomNumber]
console.log(randomName, randomNumber)

document.querySelector(".splashText").innerHTML = randomName

document.onload = loaded()

function loaded(){
    setTimeout(function(){
        document.querySelector(".loading").classList.add("dissapear")
        document.querySelector(".loader").classList.add("dissapear")
    }, 3000) 
    setTimeout(function(){
        document.querySelector(".loading").style.visibility = "hidden"
        document.querySelector(".loader").style.visibility = "hidden"
    }, 5000)
}