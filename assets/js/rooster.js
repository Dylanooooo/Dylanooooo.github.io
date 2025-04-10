<script>
    function laadRooster() {
        fetch('../includes/getRooster.php')
            .then(response => response.json()) // Zorg dat getRooster.php JSON retourneert
            .then(data => {
                const roosterContainer = document.getElementById('rooster');
                roosterContainer.innerHTML = ''; // Maak de container leeg

                // Maak een tabel
                const table = document.createElement('table');
                table.classList.add('rooster-tabel');

                // Voeg de tabelkop toe
                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr>
                        <th>Naam</th>
                        <th>Datum</th>
                        <th>Tijd</th>
                    </tr>
                `;
                table.appendChild(thead);

                // Voeg de tabelrijen toe
                const tbody = document.createElement('tbody');
                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.Naam}</td>
                        <td>${item.Datum}</td>
                        <td>${item.Tijd}</td>
                    `;
                    tbody.appendChild(row);
                });
                table.appendChild(tbody);

                // Voeg de tabel toe aan de container
                roosterContainer.appendChild(table);
            })
            .catch(error => {
                console.error('Fout bij laden rooster:', error);
            });
    }

    laadRooster();
    setInterval(laadRooster, 10000); 
</script>

// Functie om het rooster bij te werken
function updateRooster(id) {
    const datum = document.querySelector(`.rooster-datum[data-id="${id}"]`).value;
    const tijdstip = document.querySelector(`.rooster-tijdstip[data-id="${id}"]`).value;
    const activiteit = document.querySelector(`.rooster-activiteit[data-id="${id}"]`).value;
    const medewerker = document.querySelector(`.rooster-medewerker[data-id="${id}"]`).value;

    fetch('../includes/updateRooster.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            datum: datum,
            tijdstip: tijdstip,
            activiteit: activiteit,
            medewerker: medewerker
        })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Rooster succesvol bijgewerkt');
                // Directe update in de DOM
                document.querySelector(`.rooster-datum[data-id="${id}"]`).value = datum;
                document.querySelector(`.rooster-tijdstip[data-id="${id}"]`).value = tijdstip;
                document.querySelector(`.rooster-activiteit[data-id="${id}"]`).value = activiteit;
                document.querySelector(`.rooster-medewerker[data-id="${id}"]`).value = medewerker;
            } else {
                alert('Fout bij het bijwerken van het rooster: ' + result.error || 'Onbekende fout');
            }
        })
        .catch(error => {
            console.error('Fout bij het versturen van de update:', error);
            alert('Er is een fout opgetreden bij het bijwerken van het rooster.');
        });
}

// Zoekfunctie
document.getElementById('search').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    const roosterItems = document.querySelectorAll('.rooster-item');

    roosterItems.forEach(item => {
        const medewerker = item.querySelector('.rooster-medewerker').value.toLowerCase();
        const activiteit = item.querySelector('.rooster-activiteit').value.toLowerCase();
        const datum = item.querySelector('.rooster-datum').value.toLowerCase();

        if (medewerker.includes(searchTerm) || activiteit.includes(searchTerm) || datum.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Rooster laden bij het openen van de pagina
document.addEventListener('DOMContentLoaded', function () {
    laadRooster();
});
