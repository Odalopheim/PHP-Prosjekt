<?php
require_once __DIR__ . '/../config.php';

class Geocoder {
    public static function getCoordinates($place) {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($place);
        
        $opts = [
            'http' => [
                'header' => "User-Agent: " . GEOCODER_USER_AGENT
            ]
        ];
        
        $context = stream_context_create($opts);
        $json = file_get_contents($url, false, $context);
        $data = json_decode($json, true);

        if (isset($data[0])) {
            return [
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon']
            ];
        }
        return null;
    }
}
?>