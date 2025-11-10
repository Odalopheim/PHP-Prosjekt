<?php
require_once __DIR__ . '/../models/GeoCoder.php';
require_once __DIR__ . '/../models/WeatherService.php';
require_once __DIR__ . '/../models/Conversation.php';


class ChatBot {
    public function respond($input) {
        $s = trim($input);
        if ($s === '') return "Skriv inn et sted for å få værdata.";

        // Generer kandidat-strenger fra setningen og prøv disse mot geokoder-APIet.
        $candidates = $this->generatePlaceCandidates($s);
        $coords = null;
        $foundPlace = null;

        $maxAttempts = 5;
        $attempts = 0;
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
            // Ingen kandidater ga treff
            return "Beklager, men hvilket sted mener du?";
        }

        // Hent værdata
        $weather = WeatherService::getWeather($coords['lat'], $coords['lon']);
        if (!$weather) return "Kunne ikke hente værdata for {$foundPlace}.";

        // Lag svaret (bruk det funnede sted-navnet)
        $responsePlace = $foundPlace ?? $s;
        $response = "Været i {$responsePlace} nå: {$weather['temperature']}°C, vind: {$weather['wind']} m/s, fuktighet: {$weather['humidity']}%.";

        // Lagre samtalen i databasen. Hvis brukeren er logget inn, inkluder epost.
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userEmail = $_SESSION['user_email'] ?? null;
        if ($userEmail) {
            // Sett global variabel som Conversation::saveMessage leser hvis kolonne finnes
            $GLOBALS['__conversation_user_email'] = $userEmail;
        }
        Conversation::saveMessage($responsePlace, $response);
        // Rydd opp global variabel
        unset($GLOBALS['__conversation_user_email']);

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
        $words = preg_split('/\s+/u', $s);
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
        $patterns = [
            '/\bi morgen\b/iu', '/\bimorgen\b/iu', '/\bi dag\b/iu', '/\bidag\b/iu',
            '/\bnå\b/iu', '/\bi kveld\b/iu', '/\bikveld\b/iu', '/\bi natt\b/iu',
            '/\bi morges\b/iu', '/\bi går\b/iu', '/\bigår\b/iu',
            '/\bværet\b/iu', '/\bvær\b/iu', '/\btemperatur\b/iu', '/\bnår\b/iu',
            '/\bhvordan\b/iu', '/\bblir\b/iu', '/\bskal\b/iu', '/\bregner\b/iu'
        ];
        $clean = preg_replace($patterns, ' ', $text);
        $clean = preg_replace('/\s+/u', ' ', $clean);
        return trim($clean);
    }
}
