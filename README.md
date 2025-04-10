# Flitz-Events Portal

Welkom bij het Flitz-Events Stageportaal. Dit platform is speciaal ontwikkeld voor het beheren van stagiairs, projecten en communicatie binnen Flitz-Events.

## Installatie

Volg deze stappen om het project lokaal op te zetten:

1. Clone de repository naar je lokale server (XAMPP/WAMP/MAMP) directory:
   ```
   git clone [repository-url] c:\Xampp\htdocs\Dylanooooo.github.io
   ```

2. Importeer de database:
   - Open PHPMyAdmin (http://localhost/phpmyadmin)
   - Maak een nieuwe database met de naam `flitz_events`
   - Importeer het bestand `assets/database/flitz_events.sql`

3. Configureer de database-verbinding:
   - Controleer het bestand `includes/config.php`
   - Wijzig indien nodig de databasegegevens

4. Start je webserver en navigeer naar:
   ```
   http://localhost/Dylanooooo.github.io
   ```

## Inloggegevens

### Administrator
- E-mail: admin@flitz-events.nl
- Wachtwoord: admin123

### Stagiair
- E-mail: stagiair@flitz-events.nl
- Wachtwoord: stagiair123

### Stagiair registratie
Stagiairs kunnen zichzelf registreren met de volgende informatie:
- Naam en e-mail (verplicht)
- School/instelling
- Opleiding
- Stage periode (start- en einddatum)

## Projectstructuur

```
Flitz-Events Stageportaal/
├── assets/            # Frontend bestanden
│   ├── css/           # Stylesheet bestanden
│   ├── js/            # JavaScript bestanden
│   ├── img/           # Afbeeldingen
│   └── database/      # Database scripts en updates
├── auth/              # Authenticatie scripts
│   ├── login.php      # Inlogverwerking
│   ├── register.php   # Registratieverwerking
│   └── logout.php     # Uitloggen
├── includes/          # Herbruikbare componenten
│   ├── config.php     # Database configuratie
│   ├── header.php     # Algemene header
│   ├── footer.php     # Algemene footer
│   └── navigation.php # Navigatie component
├── pages/             # Webpagina's
│   ├── admin.php      # Admin dashboard
│   ├── dashboard.php  # Gebruikersdashboard
│   ├── projecten.php  # Projectenoverzicht
│   └── project-detail.php # Projectdetailpagina
└── docs/              # Projectdocumentatie
```

## Functionaliteiten

Het Flitz-Events Stageportaal bevat de volgende hoofdfunctionaliteiten:

- Gebruikersbeheer (registratie, inloggen, rollen)
- Dashboard met projectoverzicht en taken
- Projectenbeheer met voortgangsmonitoring
- Taakbeheer binnen projecten
- Chatfunctie voor teamcommunicatie

Voor een gedetailleerd overzicht van alle functionaliteiten, zie [FEATURES.md](FEATURES.md).

## Documentatie

In de `docs` map vind je gedetailleerde documentatie over het project:

- [Overzicht](docs/overzicht.md) - Algemene projectdoelen en functionaliteiten
- [Gebruikersauthenticatie](docs/authenticatie.md) - Details over het inlog- en registratiesysteem
- [Architectuur](docs/architectuur.md) - Technische architectuur en code organisatie
- [Ontwikkelingsplan](docs/ontwikkelingsplan.md) - Toekomstige functies en ontwikkelingsmogelijkheden
- [Setup Handleiding](docs/setup.md) - Gedetailleerde instructies voor het opzetten van de ontwikkelomgeving
- [Checklist](docs/checklist.md) - Overzicht van voltooide en nog openstaande taken

## Development

### Vereisten
- PHP 8.0 of hoger
- MySQL/MariaDB
- Webserver (Apache/Nginx)

### Lokaal ontwikkelen
1. Maak wijzigingen in de betreffende bestanden
2. Test wijzigingen lokaal
3. Voer code reviews uit
4. Zorg dat alle tests slagen
5. Commit wijzigingen naar een feature branch

### Publiceren naar productie
1. Merge nooit direct naar de main branch zonder review
2. Zorg dat alle code voldoet aan de projectstandaarden
3. Controleer op beveiligingsproblemen
4. Verwijder debug code en console.log statements
5. Test de applicatie volledig in een staging omgeving
6. Gebruik pull requests voor alle wijzigingen naar main

## Bijdragen

1. Fork de repository
2. Maak een feature branch (`git checkout -b feature/amazing-feature`)
3. Commit je wijzigingen (`git commit -m 'Add some amazing feature'`)
4. Push naar de branch (`git push origin feature/amazing-feature`)
5. Open een Pull Request

## Contact

Bij vragen of problemen, neem contact op met:
- Projectbeheerder: Milan - milan@flitz-events.nl