<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';

class ChatBot
{
    /**
     * Svar på brukerinput med værdata.
     */
    public function respond(string $input): string
    {
        $s = trim($input);
        if ($s === '') {
            return "Skriv inn et sted for å få værdata.";
        }

        // Finn koordinater fra kandidatene
        $candidates  = $this->generatePlaceCandidates($s);
        $coords      = null;
        $foundPlace  = null;

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

        
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) {
            return "Kunne ikke hente værdata for {$foundPlace}.";
        }

       
        $responsePlace = $foundPlace ?? $s;
        $response      = $this->generateWeatherResponse($s, $responsePlace, $weather);

        Conversation::saveMessage($responsePlace, $response);

        return $response;
    }

    /**
     * Generer svaret basert på hva brukeren spør om.
     */
    private function generateWeatherResponse(string $input, string $place, array $weather): string
    {
        $lowerInput = mb_strtolower($input);

        if (preg_match('/regn|regner|regner det/i', $lowerInput)) {
            $rainInfo = $weather['rain'] ?? null;
            return $rainInfo
                ? "Ja, det regner i {$place} nå."
                : "Nei, det regner ikke i {$place} nå.";
        }

        if (preg_match('/temperatur|varmt|kaldt/i', $lowerInput)) {
            $temp = $weather['temperature'];
            if ($temp < 5) {
                return sprintf("Temperaturen i %s er %s°C, det er kaldt.", $place, $temp);
            }
            return sprintf("Temperaturen i %s er %s°C.", $place, $temp);
        }

        return sprintf(
            "Været i %s nå: %s°C, vind: %s m/s, fuktighet: %s%%.",
            $place,
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

        // Fjerner duplikat mellomrom
        $s = preg_replace('/\s+/u', ' ', $s);

        // Fjern spørsmålstegn/utroptegn fra slutten
        $sClean = trim($s, " \t\n\r\0\x0B?!."); 

        // Let etter stedsnavn etter vanlige preposisjoner
        if (preg_match_all('/\b(?:i|på|ved|til|inne\s+ved)\s+([^,?.!]+)/iu', $s, $matches)) {
            foreach ($matches[1] as $match) {
                $candidates[] = $this->stripTimeWords($match);
            }
        }

        // Finn egennavn (sjekker etter storbokstav)
        if (preg_match_all('/\b[ÆØÅA-Z][a-zæøå\-]+(?:\s+[ÆØÅA-Z][a-zæøå\-]+)*/u', $s, $names)) {
            foreach ($names[0] as $n) {
                $candidates[] = $this->stripTimeWords($n);
            }
        }

        // Prøv siste to ord 
        $words = preg_split('/\s+/u', $sClean);
        $count = count($words);

        if ($count >= 2) {
            $candidates[] = $this->stripTimeWords($words[$count - 2] . ' ' . $words[$count - 1]);
        }
        if ($count >= 1) {
            $candidates[] = $this->stripTimeWords($words[$count - 1]);
        }

        // Hele setningen som fallback
        $candidates[] = $this->stripTimeWords($sClean);

        // Fjern duplikater og tomme elementer, behold rekkefølge
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
     * Fjern tidsuttrykk og vanlige fyllord som ikke skal være med i stedssøk.
     */
    private function stripTimeWords(string $text): string
    {
        $pattern = '/\b(?:i morgen|imorgen|i dag|idag|nå|i kveld|ikveld|i natt|i morges|i går|igår|været|vær|temperatur|når|hvordan|blir|skal|regner)\b/iu';

        $clean = preg_replace($pattern, ' ', $text);
        $clean = preg_replace('/\s+/u', ' ', $clean);

        return trim($clean);
    }
}