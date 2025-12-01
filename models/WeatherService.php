
<?php
require_once __DIR__ . '/../config.php';

class WeatherService {
    // Henter værdata fra MET API basert på latitude og longitude
    public static function getWeather($lat, $lon) {
        $url = MET_API_URL . "?lat=$lat&lon=$lon";
        
        $opts = [
            'http' => [
                'header' => "User-Agent: " . USER_AGENT
            ]
        ];

        // Oppretter kontekst for HTTP-forespørselen med headeren
        $context = stream_context_create($opts);

        // Henter JSON-data fra MET API-et og assosiativ array
        $json = file_get_contents($url, false, $context);
        $data = json_decode($json, true);

        // Sjekk at data eksiterer
        if (!$data || !isset($data['properties']['timeseries'][0])) return null;

        // Henter de siste værmålingene fra API-responsen
        $now = $data['properties']['timeseries'][0]['data']['instant']['details'];

        // Returnerer et array med svar
        return [
            'temperature' => $now['air_temperature'] ?? 'Ukjent', 
            'wind' => $now['wind_speed'] ?? 'Ukjent',
            'humidity' => $now['relative_humidity'] ?? 'Ukjent'
        ];
    }
}

