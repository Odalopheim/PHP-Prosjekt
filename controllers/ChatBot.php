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

        // Generer kandidat-strenger fra setningen og prøv disse mot geokoder-APIet
        $candidates  = $this->generatePlaceCandidates($s);
        $coords      = null;
        $foundPlace  = null;

        $maxAttempts = 5;
        $attempts    = 0;

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') continue;

            $attempts++;
            if ($attempts > $maxAttempts) break;

            $coords = GeoCoder::getCoordinates($candidate);
            if ($coords) {
                $foundPlace = $candidate;
                break;
            }
        }

        if (!$coords) {
            return "Beklager, men hvilket sted mener du?";
        }

        // Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) {
            return "Kunne ikke hente værdata for {$foundPlace}.";
        }

        // Lag svaret (bruk det funnede sted-navnet)
        $responsePlace = $foundPlace ?? $s;
        $response = sprintf(
            "Været i %s nå: %s°C, vind: %s m/s, fuktighet: %s%%.",
            $responsePlace,
            $weather['temperature'],
            $weather['wind'],
            $weather['humidity']
        );

        // Lagre samtalen i databasen (inkluder e-post hvis bruker er logget inn)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userEmail = $_SESSION['user_email'] ?? null;
        if ($userEmail) {
            $GLOBALS['__conversation_user_email'] = $userEmail;
        }

        Conversation::saveMessage($responsePlace, $response);

        // Rydd opp global variabel
        unset($GLOBALS['__conversation_user_email']);

        return $response;
    }

    /**
     * Finne stedsnavn fra en setning. 
     * Baserer seg på normal gramatikk.
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