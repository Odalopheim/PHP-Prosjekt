<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';

class ChatBotService
{
    /**
     * Hentrer svar fra chatbot basert på brukerinput. 
     */
    public function respond(string $input): string
    {
        $s = trim($input);
        if ($s === '') {
            return "Skriv inn et sted for å få værdata.";
        }

        // Finn koordinater
        $candidates = $this->generatePlaceCandidates($s);
        $coords = null;
        $foundPlace = null;

        foreach ($candidates as $candidate) {
            $coords = GeoCoder::getCoordinates($candidate);
            if ($coords) {
                $foundPlace = $candidate;
                break;
            }
        }

        if (!$coords) {
            return "Beklager, men hvilket sted mener du?";
        }

        // Bestem om brukeren spør om i dag eller i morgen
        $offsetHours = 0;
        if (preg_match('/i morgen|imorgen/i', mb_strtolower($s))) {
            $offsetHours = 24; // 24 timer frem
        }

        // Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon'], $offsetHours);
        if (!$weather) {
            return "Kunne ikke hente værdata for {$foundPlace}.";
        }

        // Generer svar
        $responsePlace = $foundPlace; // ✅ bruk kun stedsnavn uten tidsuttrykk
        $response = $this->generateWeatherResponse($s, $responsePlace, $weather, $offsetHours);

        // Lagre samtale
        Conversation::saveMessage($responsePlace, $response);

        return $response;
    }

    /**
     * Generer svaret basert på hva brukeren spør om.
     */
    private function generateWeatherResponse(string $input, string $place, array $weather, int $offsetHours = 0): string
    {
        $lowerInput = mb_strtolower($input);
        $tidspunkt = $offsetHours === 24 ? "i morgen" : "i dag"; // ✅ hele uttrykket

        // Regn
        if (preg_match('/regn|regner|regner det/i', $lowerInput)) {
            $rainInfo = $weather['precipitation'] ?? 0;
            return $rainInfo > 0
                ? "Ja, det regner i {$place} {$tidspunkt}."
                : "Nei, det regner ikke i {$place} {$tidspunkt}.";
        }

        // Temperatur
        if (preg_match('/temperatur|varmt|kaldt/i', $lowerInput)) {
            $temp = $weather['temperature'];
            if ($temp < 5) {
                return sprintf("Temperaturen i %s %s er %s°C, som vi kan si er kaldt.", $place, $tidspunkt, $temp);
            }
            if ($temp > 19) {
                return sprintf("Temperaturen i %s %s er %s°C, som vi kan si er varmt.", $place, $tidspunkt, $temp);
            }
            return sprintf("Temperaturen i %s %s er %s°C.", $place, $tidspunkt, $temp);
        }

        // Snø
        if (preg_match('/snø|snøer|snør/i', $lowerInput)) {
            $temp = $weather['temperature'];
            $precip = $weather['precipitation'] ?? 0;
            $symbol = $weather['symbol'] ?? '';

            if ($temp < 2 && $precip > 0 && stripos($symbol, 'snow') !== false) {
                return sprintf("Ja, det snør i %s %s.", $place, $tidspunkt);
            }
            return sprintf("Nei, det snør ikke i %s %s.", $place, $tidspunkt);
        }

        // Standard svar
        return sprintf(
            "Været i %s %s: %s°C, vind: %s m/s, fuktighet: %s%%.",
            $place,
            $tidspunkt,
            $weather['temperature'],
            $weather['wind'],
            $weather['humidity']
        );
    }

    /**
     * Finne stedsnavn fra en setning.
     */
    private function generatePlaceCandidates(string $sentence): array
    {
        $candidates = [];
        $s = trim($sentence);
        if ($s === '') return $candidates;

        $s = preg_replace('/\s+/u', ' ', $s);
        $sClean = trim($s, " \t\n\r\0\x0B?!."); 

        // Preposisjoner
        if (preg_match_all('/\b(?:i|på|ved|til|inne\s+ved)\s+([^,?.!]+)/iu', $s, $matches)) {
            foreach ($matches[1] as $match) {
                $candidates[] = $this->stripTimeWords($match);
            }
        }

        // Egennavn
        if (preg_match_all('/\b[ÆØÅA-Z][a-zæøå\-]+(?:\s+[ÆØÅA-Z][a-zæøå\-]+)*/u', $s, $names)) {
            foreach ($names[0] as $n) {
                $candidates[] = $this->stripTimeWords($n);
            }
        }

        // Fjern duplikater
        $seen = [];
        $out  = [];
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
     * Fjern tidsuttrykk og vanlige fyllord fra stedsnavn.
     */
    private function stripTimeWords(string $text): string
    {
        $pattern = '/\b(?:i morgen|imorgen|i dag|idag|nå|i kveld|ikveld|i natt|i morges|i går|igår)\b/iu';

        $clean = preg_replace($pattern, ' ', $text);
        $clean = preg_replace('/\s+/u', ' ', $clean);

        return trim($clean);
    }
}