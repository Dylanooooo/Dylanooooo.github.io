let loginPage = document.querySelector(".login-page")

let loginbutton = document.querySelector(".login-login")
loginbutton.addEventListener("click", loggingIn)
let closePage = document.querySelector(".login-close")
closePage.addEventListener("click", showHideLogin)
let logIn = document.querySelector(".inloggen")
logIn.addEventListener("click", showHideLogin)

let chatbotText = document.querySelector(".chatbot-text")
let chatbotTextText = document.querySelector(".chatbot-text-text")
let displayText = document.querySelector(".display-text")

document.querySelector("main").addEventListener("click", function (event) {
    if (!event.target.classList.contains("chatbot-button")) {
        chatbotTextText.value = ""
        if (chatbotText.style.display == "grid") {
            chatbotText.style.display = "none"
            chatbotButton.textContent = "Chatbot"
        }
    }
})

function showHideLogin() {
    let showHide = loginPage.style.display
    if (showHide == "block") {
        loginPage.style.display = "none"
    } else {
        loginPage.style.display = "block"
    }
}

let inputText = document.querySelector(".input-text")
let inputEmail = document.querySelector(".input-email")
let inputPassword = document.querySelector(".input-password")

function loggingIn() {
    if (inputText.value == "" || inputEmail.value == "" || inputPassword.value == "") {
        if (inputText.value == "") {
            inputText.style.border = "solid red 3px"
            setTimeout(function () {
                inputText.style.border = "solid black 3px"
            }, 1000)
        }
        if (inputEmail.value == "") {
            inputEmail.style.border = "solid red 3px"
            setTimeout(function () {
                inputEmail.style.border = "solid black 3px"
            }, 1000)
        }
        if (inputPassword.value == "") {
            inputPassword.style.border = "solid red 3px"
            setTimeout(function () {
                inputPassword.style.border = "solid black 3px"
            }, 1000)
        }
    } else {
        showHideLogin()
        inputText.value = ""
        inputEmail.value = ""
        inputPassword.value = ""
    }
}

chatbotTextText.addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
        event.preventDefault();
        chatbotButton.click();
    }
})

let chatbotButton = document.querySelector(".chatbot-button")
chatbotButton.addEventListener("click", showHideChatbot)

function showHideChatbot() {
    chatbotText.style.display = "grid"
    chatbotButton.textContent = "Send"
    if (chatbotTextText.value.trim() != "") {
        displayText.innerHTML += `<div class="custom-text right">${chatbotTextText.value}</div>`
        displayText.scrollTop = displayText.scrollHeight - displayText.clientHeight;
        chatbotTextText.value = ""
        chatbotTextText.disabled = "true"
        setTimeout(botReact, 1000)
    }
}

function botReact() {
    displayText.innerHTML += `<div class="custom-text">Yo!</div>`
    displayText.scrollTop = displayText.scrollHeight - displayText.clientHeight;
    chatbotTextText.disabled = ""
}
