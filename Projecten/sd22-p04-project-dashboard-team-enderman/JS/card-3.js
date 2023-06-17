fetch(`https://rickandmortyapi.com/api/character`)
    .then(data => data.json())
    .then(jsonData => fetchRick(jsonData))

function fetchRick(jsonData){
    console.log(jsonData)
}