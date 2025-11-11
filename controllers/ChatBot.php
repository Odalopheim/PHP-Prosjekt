<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';


class ChatBot {
    public function respond($input) {
        $place = trim($input);
        if (!$place) return "Skriv inn et sted for å få værdata.";

        // Hent koordinater
        $coords = GeoCoder::getCoordinates($place);
        if (!$coords) return "Beklager, jeg fant ikke stedet '$place'.";

        // Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) return "Kunne ikke hente værdata for $place.";

        // Lag svaret
        $response = "Været i $place nå: {$weather['temperature']}°C, vind: {$weather['wind']} m/s, fuktighet: {$weather['humidity']}%.";

        // Lagre samtalen i databasen
        Conversation::saveMessage($place, $response);

        // Returner svaret til visningen
        return $response;
    }
 /**
     * Generer kandidat-strenger som kan representere et sted fra en hel setning.
     * Prioriterer uttrykk etter preposisjoner og egennavn, deretter korte avslutninger
     * og som fallback hele setningen.
     */
    private function generatePlaceCandidates(string $sentence): array {
        $candidates = [];
        $s = trim($sentence);
        if ($s === '') return $candidates;

        // Normaliser mellomrom
        $s = preg_replace('/\s+/u', ' ', $s);

        // Fjern spørsmålstegn/utrop fra slutten for enklere matching
        $sClean = trim($s, " \t\n\r\0\x0B?!.");

        // Først: finn tekst etter vanlige preposisjoner som oftest introduserer sted
        if (preg_match_all('/\b(?:i|på|ved|til|inne\s+ved)\s+([^,?.!]+)/iu', $s, $m)) {
            foreach ($m[1] as $match) {
                $candidates[] = $this->stripTimeWords($match);
            }
        }

        // Finn egennavn (stor bokstav-sekvenser)
        if (preg_match_all('/\b[ÆØÅA-Z][a-zæøå\-]+(?:\s+[ÆØÅA-Z][a-zæøå\-]+)*/u', $s, $names)) {
            foreach ($names[0] as $n) {
                $candidates[] = $this->stripTimeWords($n);
            }
        }

        // Prøv siste two ord og siste ett ord (ofte sted uttrykk)
        $words = preg_split('/\s+/u', $sClean);
        $count = count($words);
        if ($count >= 2) {
            $candidates[] = $this->stripTimeWords($words[$count-2] . ' ' . $words[$count-1]);
        }
        if ($count >= 1) {
            $candidates[] = $this->stripTimeWords($words[$count-1]);
        }

        // Til slutt: hele setningen som fallback (men etter korte kandidater)
        $candidates[] = $this->stripTimeWords($sClean);

        // Fjern duplikater og tomme elementer, behold rekkefølge
        $seen = [];
        $out = [];
        foreach ($candidates as $c) {
            $c = trim($c, " \t\n\r\0\x0B,.;:!?\"'()");
            if ($c === '') continue;
            $key = mb_strtolower($c);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $c;
            }
        }

        return $out;
    }

    /**
     * Fjern tidsuttrykk og vanlige fyllord som ikke skal være med i stedssøk.
     */
    private function stripTimeWords(string $text): string {
        $pattern = '/\b(?:i morgen|imorgen|i dag|idag|nå|i kveld|ikveld|i natt|i morges|i går|igår|været|vær|temperatur|når|hvordan|blir|skal|regner)\b/iu';

        $clean = preg_replace($pattern, ' ', $text);
        $clean = preg_replace('/\s+/u', ' ', $clean);
        return trim($clean);
    }
}
