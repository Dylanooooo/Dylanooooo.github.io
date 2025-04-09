# Gebruikersauthenticatie

## Overzicht
Het authenticatiesysteem vormt de basis van de toegangscontrole voor de applicatie. Het bestaat uit registratie, login, en rolgebaseerde toegangsrechten.

## Authenticatieproces

### Registratie
Nieuwe gebruikers kunnen zich registreren via het registratieformulier. Bij registratie:
- Worden gebruikersgegevens gevalideerd
- Wordt het wachtwoord veilig gehashed opgeslagen
- Krijgt de gebruiker standaard de rol 'gebruiker' tenzij anders opgegeven
- Wordt een nieuw account in de database aangemaakt

### Login
Het loginproces controleert:
- Of de gebruiker bestaat in de database
- Of het ingevoerde wachtwoord overeenkomt met de opgeslagen hash
- Welke rol de gebruiker heeft

Na succesvolle authenticatie:
- Wordt een sessie gestart
- Worden gebruikersgegevens in de sessie opgeslagen
- Wordt de gebruiker doorgestuurd naar het juiste dashboard op basis van rol

### Sessiemanagement
- Sessiegegevens worden bijgehouden voor authenticatiestatus
- Beveiligde pagina's controleren sessiegegevens voordat toegang wordt verleend
- Sessiegegevens bevatten: gebruikers-ID, naam, email en rol

## Codestructuur

### Belangrijke bestanden
- `auth/login.php` - Afhandeling van de loginprocedure
- `auth/register.php` - Afhandeling van de registratieprocedure
- `auth/logout.php` - Beëindigt de sessie en logt de gebruiker uit

### Databasestructuur
De tabel `gebruikers` bevat minimaal:
- `id` (primaire sleutel)
- `naam`
- `email` (moet uniek zijn)
- `wachtwoord` (gehashte waarde)
- `rol` (bijvoorbeeld 'gebruiker' of 'admin')

## Beveiliging
- Wachtwoorden worden gehashed opgeslagen met PHP's `password_hash()` functie
- Voorbereid SQL statements (prepared statements) worden gebruikt om SQL injectie te voorkomen
- Invoer wordt gevalideerd en opgeschoond

## Toekomstige verbeteringen
- Wachtwoord reset functionaliteit
- Twee-factor authenticatie
- Account verificatie via e-mail
- Meer gedifferentieerde gebruikersrollen