<?php
// ------------------
// CORS (React Vite)
// ------------------
//header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Origin: https://pampago.site/");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ------------------
// CONFIG
// ------------------
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/dolar_cache.json';
$cacheTime = 180; // 3 minutos

if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// ------------------
// LEER CACHE
// ------------------
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);

    // Si no expiró → devolver cache directamente
    if ($cacheData && isset($cacheData['timestamp']) && (time() - $cacheData['timestamp'] < $cacheTime)) {
        echo json_encode([
            "value" => $cacheData['value'],
            "arrow" => $cacheData['arrow']
        ]);
        exit;
    }

    $lastValue = $cacheData['value'] ?? 0;
} else {
    $lastValue = 0;
}

// ------------------
// LLAMADA API (cURL)
// ------------------
$ch = curl_init("https://dolarapi.com/v1/dolares/blue");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false, 
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        "value" => $lastValue,
        "arrow" => "→",
        "error" =>"Error cURL: " .curl_error($ch)
    ]);
    exit;
}

// ------------------
// PROCESAR DATA
// ------------------
$data = json_decode($response, true);

if (!$data || !isset($data['venta'])) {
    echo json_encode([
        "value" => $lastValue,
        "arrow" => "→",
        "error" => "No se pudo obtener el valor del dólar"
    ]);
    exit;
}

$currentValue = floatval($data['venta']);

// ------------------
// FLECHA
// ------------------
$arrow = $currentValue > $lastValue ? "↑" : ($currentValue < $lastValue ? "↓" : "→");

// ------------------
// GUARDAR CACHE
// ------------------
/*
file_put_contents($cacheFile, json_encode([
    "value" => $currentValue,
    "arrow" => $arrow,
    "timestamp" => time()
]));*/

file_put_contents(__DIR__.'/cache/debug.json', $response);

// ------------------
// RESPUESTA FINAL
// ------------------
echo json_encode([
    "value" => $currentValue,
    "arrow" => $arrow,
    "updated_at"=> date("H:i:s")
]);

?>