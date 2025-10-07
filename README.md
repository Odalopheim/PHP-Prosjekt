# PHP-Prosjekt

<?php
// ---- KONFIG ----
$myApp    = "WeatherBotPHP/1.0 (https://din-side.no; kontakt@din-side.no)";
$openAiKey = "DIN_OPENAI_API_KEY";   // <-- Sett inn din OpenAI-nøkkel

// --- 1. Trekk ut sted fra brukerens fritekst -------------------------------
function extractPlace(string $text, string $apiKey): string {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                        'Trekk ut kun stedsnavn (by/kommune/land) fra en norsk setning. ' .
                        'Svar med bare selve stedsnavnet uten punktum eller ekstra tekst.'
                ],
                ['role' => 'user', 'content' => $text]
            ]
        ])
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $http !== 200) {
        return '';
    }
    $data = json_decode($resp, true);
    return trim($data['choices'][0]['message']['content'] ?? '');
}

// --- 2. Hent koordinater fra Nominatim (OpenStreetMap) ---------------------
function geocodeCity(string $city): ?array {
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($city);
    $ch  = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "WeatherBotGeocoder/1.0"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) return null;

    $data = json_decode($response, true);
    if (!empty($data[0])) {
        return [
            'lat' => round((float)$data[0]['lat'], 4),
            'lon' => round((float)$data[0]['lon'], 4)
        ];
    }
    return null;
}

// --- 3. Hent værdata fra Met.no --------------------------------------------
function getYrForecast(float $lat, float $lon, string $userAgent): ?string {
    $url = "https://api.met.no/weatherapi/locationforecast/2.0/compact?lat={$lat}&lon={$lon}";
    $ch  = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["User-Agent: {$userAgent}"]
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200 || !$response) return null;

    $data = json_decode($response, true);
    $details = $data['properties']['timeseries'][0]['data']['instant']['details'] ?? null;
    if (!$details) return null;

    $temp = $details['air_temperature'] ?? '?';
    $wind = $details['wind_speed'] ?? '?';
    return "Det er nå {$temp} °C og vind på {$wind} m/s.";
}

// --- 4. AI som lager en hyggelig værmelding --------------------------------
function aiReply(string $prompt, string $apiKey): string {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' =>
                    'Du er en vennlig norsk værmelder. Lag korte, naturlige svar.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ])
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp === false || $http !== 200) {
        $err = curl_error($ch);
        curl_close($ch);
        return "Kunne ikke hente AI-svar (HTTP $http): $err";
    }
    curl_close($ch);

    $data = json_decode($resp, true);
    return trim($data['choices'][0]['message']['content'] ?? 'AI svarte ikke.');
}

// --- 5. “Chat”-logikk ------------------------------------------------------
$botReply = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMsg = trim($_POST['user_message'] ?? '');
    if ($userMsg !== '') {
        // Trekk ut stedsnavnet først
        $place = extractPlace($userMsg, $openAiKey);

        if ($place === '') {
            $botReply = "Fant ikke noe stedsnavn i meldingen.";
        } else {
            $coords = geocodeCity($place);
            if ($coords) {
                $forecast = getYrForecast($coords['lat'], $coords['lon'], $myApp);
                if ($forecast) {
                    $prompt = "Lag en kort norsk værmelding for {$place}. Data: {$forecast}";
                    $botReply = aiReply($prompt, $openAiKey);
                } else {
                    $botReply = "Kunne ikke hente værdata for «{$place}».";
                }
            } else {
                $botReply = "Fant ikke stedet «{$place}».";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
<meta charset="UTF-8">
<title>Yr Chatbot + AI</title>
<style>
body { font-family: Arial, sans-serif; background:#f0f0f0; padding:2rem;}
.chatbox { max-width: 500px; margin:auto; background:#fff; padding:1rem;
           border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,.1);}
.msg { margin:0.5rem 0; }
.user { text-align:right; color:#333; }
.bot  { text-align:left;  color:#0066cc; white-space:pre-wrap;}
input[type=text] { width:80%; padding:0.5rem; }
input[type=submit] { padding:0.5rem 1rem; }
</style>
</head>
<body>
<div class="chatbox">
    <h2>🌦️ Yr-værbot + AI</h2>

    <?php if ($botReply): ?>
        <p class="msg user"><strong>Du:</strong>
            <?= htmlspecialchars($_POST['user_message']) ?></p>
        <p class="msg bot"><strong>Bot:</strong>
            <?= nl2br(htmlspecialchars($botReply)) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="user_message"
               placeholder="Skriv f.eks. «Hvordan blir været i Kristiansand?»" required>
        <input type="submit" value="Send">
    </form>
</div>
</body>
</html>
