<?php
// Pega o email da URL — formato esperado: /qualquercoisa/email@dominio.com/qualquercoisa
$requestUri = $_SERVER['REQUEST_URI'];

// Regex para extrair email da URL
if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $requestUri, $matches)) {
    $email = strtolower(trim($matches[1]));
    
    // Arquivo onde os leads serão salvos (mesma pasta do script)
    $arquivo = __DIR__ . '/leads_interessados.txt';
    
    // Evita duplicatas — só salva se ainda não estiver no arquivo
    $jaExiste = false;
    if (file_exists($arquivo)) {
        $emailsSalvos = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($emailsSalvos as $linha) {
            // A linha tem formato: email|data|ip|user_agent
            $partes = explode('|', $linha);
            if (isset($partes[0]) && strtolower(trim($partes[0])) === $email) {
                $jaExiste = true;
                break;
            }
        }
    }
    
    if (!$jaExiste) {
        $data       = date('Y-m-d H:i:s');
        $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent  = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $referer    = $_SERVER['HTTP_REFERER'] ?? 'direct';
        
        // Formato: email|data|ip|user_agent|referer
        $linha = $email . '|' . $data . '|' . $ip . '|' . $userAgent . '|' . $referer . PHP_EOL;
        
        // LOCK_EX evita corrupção em acessos simultâneos
        file_put_contents($arquivo, $linha, FILE_APPEND | LOCK_EX);
    }
}

// --- Redirecionamento original ---
$baseUrl = 'https://www.okieweb.com/';

function generateSegment($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
}

$urls = [
    $baseUrl . generateSegment(8) . '/' . generateSegment(8) . '/' . generateSegment(8),
    $baseUrl . generateSegment(8) . '/' . generateSegment(8) . '/' . generateSegment(8),
];

$randomUrl = $urls[array_rand($urls)];
header("Location: " . $randomUrl);
exit();
?>
