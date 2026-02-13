# Værassistent - PHP Chatbot

En PHP-basert chatbot som gir deg værinformasjon for et hvilket som helst sted i Norge og verden. Applikasjonen bruker [MET API](https://api.met.no/) for værdata.

## Funksjoner

- **Værspørringer**: Spør om været for et sted, og få informasjon om temperatur, vind, fuktighet og nedbør.
- **Naturlig språk**: Støtter spørsmål som "Regner det i Oslo?" eller "Hvordan er temperaturen i Bergen i morgen?"
- **Brukerautentisering**: Registrer deg og logg inn for å bruke tjenesten.
- **Samtalehistorikk**: Se tidligere samtaler og værspørringer.

## Prosjektstruktur

Applikasjonen følger MVC-arkitektur (Model-View-Controller):

```
├── config.php          # Konfigurasjon og miljøvariabler
├── controllers/        # Kontrollere for forretningslogikk
├── models/             # Databasemodeller og datalagring
├── services/           # Tjenester for vær-API og geokoding
├── views/              # HTML-maler og brukergrensesnitt
└── public/             # Statiske filer (CSS, bilder) og index.php
```

## Installasjon

### Forutsetninger

- PHP 7.4 eller nyere
- MySQL/MariaDB database
- Webserver (Apache, Nginx eller innebygd PHP-server)

### Oppsett

1. **Klon prosjektet** og naviger til mappen.

2. **Opprett database** med navn `chatbot` (eller oppdater `.env` med egne innstillinger).

3. **Kjør SQL for å opprette tabeller:**

```sql
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_input TEXT NOT NULL,
    bot_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

4. **Konfigurer `.env`-filen** med databaseinnstillinger:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=chatbot
DB_USER=root
DB_PASS=ditt_passord
```

5. **Start webserveren** og åpne `public/index.php` i nettleseren.

## Bruk

1. Registrer deg eller logg inn.
2. Skriv inn et stedsnavn i chatvinduet, f.eks. "Oslo" eller "Hvordan er været i Trondheim?".
3. Chatboten svarer med aktuell værinformasjon.

## Teknologier

- PHP (MVC-arkitektur)
- MySQL/MariaDB
- [MET API](https://api.met.no/) for værdata
- [Nominatim](https://nominatim.org/) for geokoding

