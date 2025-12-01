<?php
require_once __DIR__ . '/../config.php';

class WeatherService {
    // Henter værdata fra MET API basert på latitude og longitude.
    public static function getWeather($lat, $lon, $offsetHours = 0) {
        $url = MET_API_URL . "?lat=$lat&lon=$lon";

        $opts = [
            'http' => [
                'header' => "User-Agent: " . USER_AGENT
            ]
        ];

        // Opprett kontekst for HTTP-forespørselen
        $context = stream_context_create($opts);

        $json = file_get_contents($url, false, $context);
        $data = json_decode($json, true);

        // Sjekk at data finnes og at timeseries er tilgjengelig
        if (!$data || !isset($data['properties']['timeseries'])) {
            return null;
        }

        $timeseries = $data['properties']['timeseries'];

        // Velg tidspunkt basert på nå
        if ($offsetHours === 0) {
            $entry = $timeseries[0];
        } else {
            // Beregn tidspunkt etter ISO standard
            $targetTime = date('c', strtotime("+$offsetHours hours"));
            $entry = null;

            // Finn første timeserie som matcher datoen til targetTime
            foreach ($timeseries as $ts) {
                if (strpos($ts['time'], substr($targetTime, 0, 10)) !== false) {
                    $entry = $ts;
                    break;
                }
            }

            if (!$entry) 
                return null;
        }

        // Hent detaljer fra målingene (temperatur, vind, fuktighet)
        $details = $entry['data']['instant']['details'];

        // Hent nedbør fra neste time eller neste 6 timer
        $precip = $entry['data']['next_1_hours']['details']['precipitation_amount']
            ?? $entry['data']['next_6_hours']['details']['precipitation_amount']
            ?? 0;

        $symbol = $entry['data']['next_1_hours']['summary']['symbol_code']
            ?? $entry['data']['next_6_hours']['summary']['symbol_code']
            ?? null;

        // Returner værdata
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