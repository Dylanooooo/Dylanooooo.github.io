# Flitz-Events Portal

Welkom bij het Flitz-Events Stageportaal. Dit platform is speciaal ontwikkeld voor het beheren van stagiairs, projecten en communicatie binnen Flitz-Events.

## Installatie

Volg deze stappen om het project lokaal op te zetten:

1. Clone de repository naar je lokale server (XAMPP/WAMP/MAMP) directory:
   ```
   git clone [repository-url] d:\Xampp\htdocs\Dylanooooo.github.io
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
- E-mail: admin@flitz.nl
- Wachtwoord: admin123

### Stagiair
- E-mail: test@mail.com
- Wachtwoord: stage

## Bestandsstructuur

Zie [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) voor een gedetailleerd overzicht van de projectstructuur.

## Functionaliteiten

Zie [FEATURES.md](FEATURES.md) voor een gedetailleerd overzicht van de functionaliteiten.

## Development

### Vereisten
- PHP 8.0 of hoger
- MySQL/MariaDB
- Webserver (Apache/Nginx)

### Lokaal ontwikkelen
1. Maak wijzigingen in de betreffende bestanden
2. Test wijzigingen lokaal
3. Commit wijzigingen naar de repository

## Bijdragen

1. Fork de repository
2. Maak een feature branch (`git checkout -b feature/amazing-feature`)
3. Commit je wijzigingen (`git commit -m 'Add some amazing feature'`)
4. Push naar de branch (`git push origin feature/amazing-feature`)
5. Open een Pull Request

## Contact

Bij vragen of problemen, neem contact op met:
- Projectbeheerder: [Naam] - [email@flitz.nl]
