
let currency = document.querySelector(".inputCurrency")

function fetchFunction(moneyLink){
    fetch(moneyLink)
        .then(data => data.json())
        .then(jsonData => fetchMoney(jsonData))
}

function formatMoneyLink(currencyCode){
    return `https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies/${currencyCode}.json`
}

function fetchMoney(jsonData){
    console.log(jsonData)
}

let knop = document.querySelector(".knop")
knop.addEventListener("click", function(){
    fetchFunction(formatMoneyLink(currency.value))
})