<?php  
require_once __DIR__ . '/../config.php';

class WeatherService {
    // Henter værdata fra MET API basert på latitude og longitude nå 

    public static function getWeather($lat, $lon) {
        $url = MET_API_URL . "?lat=$lat&lon=$lon";
        
        $opts = [
            'http' => [
                'header' => "User-Agent: " . USER_AGENT
            ]
        ];

        $context = stream_context_create($opts);
        $json = file_get_contents($url, false, $context);
        $data = json_decode($json, true);


        // Sjekk at data eksiterer

        if (!$data || !isset($data['properties']['timeseries'][0])) return null;

        $ts = $data['properties']['timeseries'][0]['data'];

        // Instant‑målinger
        $now = $ts['instant']['details'];

        // Nedbør (fra neste 1 time hvis tilgjengelig)
        $precip = $ts['next_1_hours']['details']['precipitation_amount'] ?? 0;
        $symbol = $ts['next_1_hours']['summary']['symbol_code'] ?? null;

        return [
            'temperature' => $now['air_temperature'] ?? 'Ukjent', 
            'wind'        => $now['wind_speed'] ?? 'Ukjent',
            'humidity'    => $now['relative_humidity'] ?? 'Ukjent',
            'precipitation' => $precip,
            'symbol'        => $symbol
        ];
    }

    // Henter værdata fra MET API basert på latitude og longitude nå i morgen
    public static function getWeatherForTomorrow($lat, $lon) {
    $url = MET_API_URL . "?lat=$lat&lon=$lon";
    $opts = ['http' => ['header' => "User-Agent: " . USER_AGENT]];
    $context = stream_context_create($opts);
    $json = file_get_contents($url, false, $context);
    $data = json_decode($json, true);

    if (!$data || !isset($data['properties']['timeseries'])) return null;

    $timeseries = $data['properties']['timeseries'];
    $targetTime = date('c', strtotime('+1 day')); // ISO‑tid for i morgen

    foreach ($timeseries as $entry) {
        if (strpos($entry['time'], substr($targetTime, 0, 10)) !== false) {
            $details = $entry['data']['instant']['details'];
            $precip  = $entry['data']['next_6_hours']['details']['precipitation_amount'] ?? 0;
            $symbol  = $entry['data']['next_6_hours']['summary']['symbol_code'] ?? null;

            return [
                'temperature'   => $details['air_temperature'] ?? 'Ukjent',
                'wind'          => $details['wind_speed'] ?? 'Ukjent',
                'humidity'      => $details['relative_humidity'] ?? 'Ukjent',
                'precipitation' => $precip,
                'symbol'        => $symbol,
                'time'          => $entry['time']
            ];
        }
    }

    return null;
}
}