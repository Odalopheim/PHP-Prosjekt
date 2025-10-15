# PHP-Prosjekt


# PHP-Prosjekt
Models (modell)
</br>
Hva det er:
Her ligger all logikken for data og regler.

Hva du legger her:

Klasser som beskriver hvordan dataen ser ut (for eksempel en User, Product, Post, osv.)

Kobling mot databasen (tabeller, relasjoner).

Funksjoner for å hente, lagre, oppdatere og slette data.

Kort sagt:
Alt som handler om data og forretningslogikk
</br>
Views

Dette er det brukeren ser – altså grensesnittet (HTML-sider eller JSON-data for API).

Hva du legger her:

HTML-filer (maler/templates)

Presentasjonslogikk (hvordan data vises)

Kort sagt:
Alt som handler om utseende og presentasjon.
</br>
Controllers (kontrollere)

Hva det er:
Koblingen mellom brukeren og modellene.

Hva du legger her:

Funksjoner som tar imot forespørsler (requests)

Henter data fra modellene

Sender data til riktige views

Kort sagt:
Alt som handler om flyt og logikk mellom bruker, data og visning.

Public (eller static / wwwroot)

Hva det er:
Dette er filer som alle kan nå direkte i nettleseren.

Hva du legger her:

Bilder (.png, .jpg)

CSS-filer (stil)

JavaScript-filer (klientkode)

Ikoner, fonter, osv.

Kort sagt:
Alt som er statisk innhold tilgjengelig for brukeren. 


CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_input TEXT NOT NULL,
    bot_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Brukertabell for autentisering
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Instruksjoner:
- Opprett databasen som `chatbot` eller oppdater `models/Database.php` med egne DB-innstillinger.
- Kjør SQL-en over for å lage `conversations` og `users` tabellene.

