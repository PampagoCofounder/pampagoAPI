<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
//header("Access-Control-Allow-Origin: https://pampago.site/");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
// ------------------
// CONFIG
// ------------------
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/riesgo_cache.json';
$cacheTime = 180; // 3 minutos

if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// ------------------
// LEER CACHE
// ------------------
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);

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
// API
// ------------------
$url = "https://api.argentinadatos.com/v1/finanzas/indices/riesgo-pais";

$response = file_get_contents($url);
$data = json_decode($response, true);

if (!$data || !is_array($data)) {
    echo json_encode([
        "value" => $lastValue,
        "arrow" => "→",
        "error" => "No se pudo obtener riesgo país"
    ]);
    exit;
}

// ------------------
// BUSCAR MÁS RECIENTE
// ------------------
$latest = null;

foreach ($data as $item) {
    if (
        isset($item['fecha'], $item['valor']) &&
        (!$latest || strtotime($item['fecha']) > strtotime($latest['fecha']))
    ) {
        $latest = $item;
    }
}

if (!$latest) {
    echo json_encode([
        "value" => $lastValue,
        "arrow" => "→",
        "error" => "No se encontraron datos"
    ]);
    exit;
}

$currentValue = intval($latest['valor']);

// ------------------
// FLECHA
// ------------------
$arrow = $currentValue > $lastValue ? "↑" : ($currentValue < $lastValue ? "↓" : "→");

// ------------------
// GUARDAR CACHE
// ------------------
file_put_contents($cacheFile, json_encode([
    "value" => $currentValue,
    "arrow" => $arrow,
    "timestamp" => time()
]));

// ------------------
// RESPUESTA FINAL
// ------------------
echo json_encode([
    "value" => $currentValue,
    "arrow" => $arrow
]);