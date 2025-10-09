<?php
require_once 'geoCoder.php';
require_once 'weatherService.php';

class ChatBot {
    public function respond($input) {
        $place = trim($input);
        if (!$place) return "Skriv inn et sted for å få værdata.";

        $coords = geoCoder::getCoordinates($place);
        if (!$coords) return "Beklager, jeg fant ikke stedet '$place'.";

        $weather = weatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) return "Kunne ikke hente værdata for $place.";

        return "Været i $place nå: {$weather['temperature']}°C, vind: {$weather['wind']} m/s, fuktighet: {$weather['humidity']}%.";
    }
}
