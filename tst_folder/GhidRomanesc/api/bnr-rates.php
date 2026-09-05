<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$cacheFile = __DIR__ . '/../data/bnr-rates-cache.json';
$cacheTTL  = 4 * 3600;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

$ctx = stream_context_create([
    'http' => [
        'timeout'    => 10,
        'user_agent' => 'GhidRomanesc/1.0',
    ],
    'ssl'  => ['verify_peer' => false],
]);

$raw = @file_get_contents('https://www.bnr.ro/nbrfxrates.xml', false, $ctx);

if (!$raw) {
    if (file_exists($cacheFile)) {
        echo file_get_contents($cacheFile);
        exit;
    }
    http_response_code(503);
    echo json_encode(['error' => 'Nu am putut contacta BNR.']);
    exit;
}

// Eliminăm namespace-ul default ca să putem accesa elementele direct
$raw = preg_replace('/ xmlns="[^"]*"/', '', $raw);
$dom = simplexml_load_string($raw);

if (!$dom) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare la parsarea XML BNR.']);
    exit;
}

$rates = ['RON' => ['rate' => 1.0, 'multiplier' => 1]];
$date  = '';

$cube = $dom->Body->Cube;
$date = (string)$cube['date'];

foreach ($cube->Rate as $r) {
    $currency   = (string)$r['currency'];
    $multiplier = (int)($r['multiplier'] ?? 1) ?: 1;
    $value      = (float)(string)$r;
    if ($currency && $value > 0) {
        $rates[$currency] = ['rate' => $value, 'multiplier' => $multiplier];
    }
}

$result = json_encode([
    'date'    => $date,
    'rates'   => $rates,
    'updated' => date('Y-m-d H:i'),
], JSON_UNESCAPED_UNICODE);

file_put_contents($cacheFile, $result);
echo $result;
