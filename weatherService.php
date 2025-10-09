<?php
require_once 'config.php';

class WeatherService {
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

        if (!$data || !isset($data['properties']['timeseries'][0])) return null;

        $now = $data['properties']['timeseries'][0]['data']['instant']['details'];
        return [
            'temperature' => $now['air_temperature'] ?? 'Ukjent',
            'wind' => $now['wind_speed'] ?? 'Ukjent',
            'humidity' => $now['relative_humidity'] ?? 'Ukjent'
        ];
    }
}
