<?php
// Emulacja reguł .htaccess dla wbudowanego serwera PHP (tylko do testów).
$root = $_SERVER['DOCUMENT_ROOT'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/app(/|$)#', $path)) { http_response_code(403); exit('403'); }
if ($path === '/robots.txt') { require $root . '/robots.php'; exit; }
if ($path === '/sitemap.xml') { require $root . '/sitemap.php'; exit; }
if ($path !== '/' && is_file($root . $path)) return false;
if ($path === '/') { require $root . '/index.php'; exit; }
$slug = trim($path, '/');
if (preg_match('/^[a-z0-9-]+$/', $slug) && is_file("$root/$slug.php")) { require "$root/$slug.php"; exit; }
require $root . '/404.php';
