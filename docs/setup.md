# Setup Handleiding

## Ontwikkelomgeving Opzetten

### Vereisten
- [XAMPP](https://www.apachefriends.org/) (bevat Apache, MySQL, PHP)
- Git
- Een code editor (bijv. Visual Studio Code, PhpStorm)

### Stap 1: XAMPP Installatie
1. Download XAMPP via [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Installeer XAMPP volgens de installatie-instructies voor je besturingssysteem
3. Start de XAMPP Control Panel en activeer de Apache en MySQL services

### Stap 2: Project Installatie
1. Open de terminal of command prompt
2. Navigeer naar de htdocs directory van je XAMPP installatie:
   ```
   cd c:\xampp\htdocs
   ```
3. Clone het project repository:
   ```
   git clone https://github.com/Dylanooooo/Dylanooooo.github.io.git
   ```
   of download en pak het projectarchief uit in de htdocs map

### Stap 3: Database Configuratie
1. Open de XAMPP Control Panel en klik op "Admin" naast MySQL (dit opent phpMyAdmin)
2. Maak een nieuwe database aan genaamd `flitz_events`
3. Importeer het database schema uit het bestand `[database_schema_bestand]` (indien beschikbaar)
   of voer de SQL-commando's uit die in `architectuur.md` worden vermeld
4. Configureer de database inloggegevens:
   - Open het bestand `includes/config.php`
   - Pas de database instellingen aan indien nodig:
     ```php
     $host = 'localhost';
     $db = 'flitz_events';
     $user = 'root';  // standaard XAMPP gebruikersnaam
     $pass = '';      // standaard XAMPP wachtwoord (leeg)
     ```

### Stap 4: Project Testen
1. Open je webbrowser
2. Ga naar `http://localhost/Dylanooooo.github.io/`
3. De startpagina van het project zou nu zichtbaar moeten zijn

## Troubleshooting

### Veelvoorkomende problemen

#### Apache start niet
- Controleer of poort 80 niet in gebruik is door andere software
- Controleer foutlogs in XAMPP Control Panel

#### Database verbindingsproblemen
- Controleer of de MySQL service draait
- Controleer de database instellingen in `includes/config.php`
- Zorg ervoor dat de database bestaat

#### Toegangsfouten
- Zorg ervoor dat alle bestandspermissies juist zijn ingesteld
- XAMPP heeft mogelijk lees- en schrijfrechten nodig voor bepaalde directories

## Development Workflow

### Lokale Ontwikkeling
1. Maak een nieuwe branch voor je feature/bugfix:
   ```
   git checkout -b feature/naam-van-feature
   ```
2. Maak je wijzigingen
3. Test je wijzigingen lokaal
4. Commit je wijzigingen:
   ```
   git add .
   git commit -m "Beschrijving van wijzigingen"
   ```
5. Push je branch:
   ```
   git push origin feature/naam-van-feature
   ```

### Code Stijl en Conventies
- Volg de bestaande code stijl en structuur
- Documenteer je code waar nodig
- Schrijf duidelijke commit berichten

## Deployment

### Naar Productie
Voor het deployen naar een productieomgeving:
1. Zorg ervoor dat alle debug-code is verwijderd
2. Configureer een productiedatabase met veilige inloggegevens
3. Pas de config.php aan voor productie-instellingen
4. Upload bestanden naar de productieserver via FTP of deploymenttool

## Contact & Hulp
Bij vragen of problemen, neem contact op met Milan (stagebegeleider).