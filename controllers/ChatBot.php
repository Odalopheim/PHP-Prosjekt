<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';


class ChatBot {
    public function respond($input) {
        $place = trim($input);
        if (!$place) return "Skriv inn et sted for å få værdata.";

        // Prøv å finne et sted i hele setningen ved å teste flere kandidat-strenger
        $candidates = $this->generatePlaceCandidates($place);
        $coords = null;
        $foundPlace = null;
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') continue;
            $coords = GeoCoder::getCoordinates($candidate);
            if ($coords) {
                $foundPlace = $candidate;
                break;
            }
        }

        if (!$coords) {
            // Ingen kandidater ga treff
            return "Beklager, men hvilket sted mener du?";
        }

        // Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) return "Kunne ikke hente værdata for $place.";

    // Lag svaret (bruk det funnede sted-navnet)
    $responsePlace = $foundPlace ?? $place;
    $response = "Været i $responsePlace nå: {$weather['temperature']}°C, vind: {$weather['wind']} m/s, fuktighet: {$weather['humidity']}%.";

    // Lagre samtalen i databasen
    Conversation::saveMessage($responsePlace, $response);

        // Returner svaret til visningen
        return $response;
    }

    /**
     * Generer en liste av kandidat-strenger som kan representere et sted
     * fra en hel setning. Vi prøver flere heuristikker: etter preposisjoner
     * som 'i', 'på', 'ved', hele setningen, siste ord/par av ord osv.
     * Returnerer array med kandidater i prioritert rekkefølge.
     */
    private function generatePlaceCandidates(string $sentence): array {
        $candidates = [];
        $s = trim($sentence);
        if ($s !== '') $candidates[] = $s;

        // Søk etter mønster 'i {sted}' eller 'på {sted}' eller 'ved {sted}'
        if (preg_match_all('/\b(?:i|på|ved)\s+([^,?.!]+)/iu', $s, $m)) {
            foreach ($m[1] as $match) {
                $candidates[] = trim($match);
            }
        }

        // Prøv siste to ord og siste ett ord
        $words = preg_split('/\s+/u', $s);
        $count = count($words);
        if ($count >= 2) {
            $candidates[] = $words[$count-2] . ' ' . $words[$count-1];
        }
        if ($count >= 1) {
            $candidates[] = $words[$count-1];
        }

        // Forsøk å finne ord som ser ut som egennavn (stor bokstav)
        if (preg_match_all('/\b[ÆØÅA-Z][a-zæøå]+(?:\s+[ÆØÅA-Z][a-zæøå]+)*/u', $s, $names)) {
            foreach ($names[0] as $n) $candidates[] = $n;
        }

        // Fjern duplikater, behold rekkefølge
        $seen = [];
        $out = [];
        foreach ($candidates as $c) {
            $c = trim($c, " \t\n\r\0\x0B,.;:!?\"'");
            if ($c === '') continue;
            $key = mb_strtolower($c);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $c;
            }
        }
        return $out;
    }
}
