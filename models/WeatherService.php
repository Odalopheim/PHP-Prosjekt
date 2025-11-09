
<?php
require_once __DIR__ . '/../config.php';

class WeatherService {
    //hetner værdata fra MET API basert på latitude og longitude
    public static function getWeather($lat, $lon) {
        $url = MET_API_URL . "?lat=$lat&lon=$lon";
        
        $opts = [
            'http' => [
                'header' => "User-Agent: " . USER_AGENT
            ]
        ];

        // Oppretter kontekst for HTTP-forespørselen med headeren
        $context = stream_context_create($opts);

        // Henter JSON-data fra MET API-et
        $json = file_get_contents($url, false, $context);

        // Dekoder JSON-responsen til en assosiativ array
        $data = json_decode($json, true);

        // Returnerer null hvis ingen data eller feil struktur i svaret
        if (!$data || !isset($data['properties']['timeseries'][0])) return null;

        // Henter de siste tilgjengelige værmålingene fra API-responsen
        $now = $data['properties']['timeseries'][0]['data']['instant']['details'];

        // Returnerer et array med temperatur, vind og luftfuktighet
        return [
            'temperature' => $now['air_temperature'] ?? 'Ukjent', // Hvis ikke funnet, returner 'Ukjent'
            'wind' => $now['wind_speed'] ?? 'Ukjent',
            'humidity' => $now['relative_humidity'] ?? 'Ukjent'
        ];
    }
}

