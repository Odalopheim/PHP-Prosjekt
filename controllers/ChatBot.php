<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';


class ChatBot {
    public function respond($input) {
        $place = trim($input);
        if (!$place) return "Skriv inn et sted for å få værdata.";

        // 🔍 Hent koordinater
        $coords = GeoCoder::getCoordinates($place);
        if (!$coords) return "Beklager, jeg fant ikke stedet '$place'.";

        // 🌦️ Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) return "Kunne ikke hente værdata for $place.";

        // ✍️ Lag svaret
        $response = "Været i $place nå: {$weather['temperature']}°C, vind: {$weather['wind']} m/s, fuktighet: {$weather['humidity']}%.";

        // 💾 Lagre samtalen i databasen
        Conversation::saveMessage($place, $response);

        // 📤 Returner svaret til visningen
        return $response;
    }
}
