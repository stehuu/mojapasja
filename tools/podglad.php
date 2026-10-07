<?php
// Lokalny podgląd strony: php -S localhost:8000 -t public_html tools/podglad.php
// Wbudowany serwer PHP nie czyta .htaccess – tu odtwarzamy potrzebne reguły.
$root = $_SERVER['DOCUMENT_ROOT'];
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (preg_match('#^/(private_inquiries|api/config\.php)#', $path)) {
    http_response_code(403);
    exit('403');
}
if (preg_match('#^/room-photos/[^/]+\.(jpe?g|png|webp)$#', $path) && !is_file($root . $path)) {
    header('Content-Type: image/jpeg');
    readfile($root . '/images/zdjecie-wkrotce.jpg');
    exit;
}
if (is_file($root . $path) || is_file(rtrim($root . $path, '/') . '/index.html')) {
    return false;
}
http_response_code(404);
readfile($root . '/404.html');
