# Flitz-Events Stageportaal - Functies en Roadmap

## Huidige Functionaliteiten

### Gebruikersbeheer
- **Registratie**: Nieuwe stagiaires kunnen een account aanmaken met persoonlijke en opleidingsgegevens
- **Inloggen**: Beveiligde toegang tot het platform met e-mail en wachtwoord
- **Rollen**: Onderscheid tussen stagiairs en admins (beheerders)
- **Uitloggen**: Veilig uitloggen uit het systeem

### Dashboard
- **Welkomstbanner**: Informatieve banner met belangrijke informatie
- **Projectoverzicht**: Weergave van het actieve project met voortgangsmeter
- **Takenlijst**: Overzicht van openstaande taken met deadlines
- **Aankomende shifts**: Weergave van geplande werkdagen
- **Team updates**: Belangrijke mededelingen van het team
- **Snelle links**: Directe toegang tot veelgebruikte pagina's
- **Voortgangsmeters**: Visualisatie van competentie-ontwikkeling

### Projecten
- **Projectenoverzicht**: Grid-weergave van alle projecten
- **Projectstatussen**: Aankomend, actief, afgerond
- **Voortgangsindicatoren**: Visualisatie van projectvoortgang
- **Teamleden**: Weergave van betrokken teamleden per project
- **Filtering**: Mogelijkheid om projecten te filteren op status

### Chat
- **Privégesprekken**: Directe communicatie tussen gebruikers
- **Groepsgesprekken**: Communicatie binnen projectteams
- **Berichtstatussen**: Weergave van gelezen/ongelezen berichten
- **Chatzoekfunctie**: Zoeken binnen gesprekken
- **Responsive design**: Werkt op zowel desktop als mobiele apparaten

### UI/UX
- **Responsive design**: Optimaal gebruik op verschillende schermgroottes
- **Intuïtieve navigatie**: Duidelijke menustructuur
- **Consistente styling**: Herkenbare Flitz-Events branding
- **Toegankelijkheid**: Goede leesbaarheid en contrast

## Geplande Functionaliteiten (In Ontwikkeling)

### Trainingen & Ontwikkeling
- **Trainingsmateriaal**: Online beschikbare leermaterialen
- **Cursusmodules**: Gestructureerde leertrajecten
- **Voortgangstests**: Mogelijkheid om kennis te toetsen
- **Certificaten**: Digitale bewijzen van afgeronde trainingen

### Roostering
- **Persoonlijk rooster**: Overzicht van geplande werkdagen
- **Beschikbaarheid doorgeven**: Stagiairs kunnen aangeven wanneer ze beschikbaar zijn
- **Verlofaanvragen**: Mogelijkheid om vrije dagen aan te vragen
- **Kalenderintegratie**: Exporteren naar persoonlijke agenda's

### Admin Dashboard
- **Gebruikersbeheer**: Beheren van stagiairs en medewerkers
- **Projectaanmaak**: Nieuwe projecten toevoegen en configureren
- **Taaktoewijzing**: Taken toewijzen aan specifieke stagiairs
- **Rapportages**: Genereren van voortgangs- en aanwezigheidsrapporten
- **Beoordelingen**: Systeem voor evaluatie van stagiairs

### Voortgangsbewaking
- **Competentieprofielen**: Definiëren van te ontwikkelen competenties
- **Beoordelingscriteria**: Heldere criteria voor evaluatie
- **Feedback-momenten**: Geplande evaluatiemomenten
- **Ontwikkelingsplan**: Persoonlijke groeidoelen

## Toekomstige Uitbreidingen (Roadmap)

### Documentatie & Kennisbank
- **Stagegids online**: Digitale versie van de stagegids
- **Procedures**: Documentatie van werkprocessen
- **FAQ**: Veelgestelde vragen en antwoorden
- **Sjablonen**: Herbruikbare documenten

### Mobiele Verbeteringen
- **Push notificaties**: Directe meldingen voor belangrijke updates
- **Offline functionaliteit**: Beperkte toegang zonder internet

### Integraties
- **Kalenderintegratie**: Synchronisatie met Google Calendar, Outlook, etc.
- **Documenten koppeling**: Integratie met OneDrive/Google Drive
- **E-mail notificaties**: Automatische notificaties voor belangrijke gebeurtenissen

### Analyse & Rapportage
- **Voortgangsrapporten**: Gedetailleerde inzichten in ontwikkeling
- **Aanwezigheidsregistratie**: Monitoring van gewerkte uren
- **Exportfuncties**: Data exporteren naar Excel/PDF

## Benodigde Database Uitbreidingen

De volgende tabellen zijn nodig om bovenstaande functies te implementeren:

1. **projecten**: Voor projectbeheer en -weergave
2. **taken**: Voor taakbeheer binnen projecten
3. **roosters**: Voor planning van werkdagen
4. **berichten**: Voor de chatfunctionaliteit
5. **groepsgesprekken**: Voor projectteam communicatie
6. **updates**: Voor algemene mededelingen
7. **competenties**: Voor voortgangsbewaking van stagiaires

## Technische Architectuur

### Frontend
- HTML5, CSS3, JavaScript
- Responsive design
- Progressive enhancement 

### Backend
- PHP 8.x
- MySQL/MariaDB
- PDO voor databaseconnecties

### Beveiliging
- Wachtwoord hashing met Bcrypt
- Bescherming tegen SQL-injectie
- Session management
- Role-based access control
