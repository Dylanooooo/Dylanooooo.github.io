# Technische Architectuur

## Overzicht
Dit document beschrijft de technische architectuur van de webapplicatie, inclusief de mappenstructuur, database opzet en de belangrijkste codepaden.

## Mappenstructuur

Dylanooooo.github.io/
├── auth/              # Authenticatie scripts
│   ├── login.php      # Inlogverwerking
│   ├── register.php   # Registratieverwerking
│   └── logout.php     # Uitloggen
├── includes/          # Herbruikbare componenten
│   ├── config.php     # Database configuratie
│   ├── header.php     # Algemene header
│   └── footer.php     # Algemene footer
├── pages/             # Webpagina's
│   ├── admin.php      # Admin dashboard
│   ├── dashboard.php  # Gebruikersdashboard
│   └── [andere pagina's]
├── assets/            # Frontend bestanden
│   ├── css/           # Stylesheet bestanden
│   ├── js/            # JavaScript bestanden
│   └── img/           # Afbeeldingen
├── docs/              # Documentatie
└── index.php          # Homepage/startpagina

## Codestructuur en Werking

### Database Connectie
De database connectie wordt geconfigureerd in `includes/config.php`. Hier wordt PDO gebruikt voor veilige databasetoegang. Dit bestand wordt geïncludeerd in alle PHP bestanden die database-toegang nodig hebben.

### Authenticatie Flow
1. Gebruiker bezoekt `index.php` met login formulier
2. Na inloggen wordt data verwerkt door `auth/login.php`
3. Bij succes wordt de gebruiker naar het juiste dashboard geleid op basis van rol
4. Sessies worden gebruikt om de inlogstatus te behouden

### Sessie Management
- Sessies worden gestart met `session_start()` aan het begin van elke pagina
- Gebruikersgegevens worden opgeslagen in `$_SESSION` variabelen
- Beveiliging van pagina's gebeurt door sessie te controleren

### Frontend
- Gebruik van HTML5, CSS en JavaScript
- [Informatie over eventuele frameworks zoals Bootstrap, jQuery]
- Responsief ontwerp voor verschillende schermformaten

## Database Schema

### Tabel: gebruikers
```sql
CREATE TABLE gebruikers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  naam VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  wachtwoord VARCHAR(255) NOT NULL,
  rol ENUM('gebruiker', 'admin') DEFAULT 'gebruiker',
  datum_aangemaakt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

[Voeg hier andere tabellen toe indien van toepassing]

## Technische Keuzes

### Server-side
- PHP voor back-end logica
- PDO voor database-interacties
- Password hashing voor veilige wachtwoordopslag

### Client-side
- [Informatie over gebruikte JavaScript libraries/frameworks]
- [Informatie over CSS frameworks/methodologieën]

## Deployment
De applicatie draait momenteel op een lokale XAMPP-omgeving. Voor productie zou een gehoste webserver met PHP en MySQL ondersteuning nodig zijn.