<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

if (!cfg('indexable')) {
    // Domena robocza – nie indeksujemy.
    echo "User-agent: *\nDisallow: /\n";
    exit;
}

echo "User-agent: *\nAllow: /\n\nSitemap: " . absolute_url('sitemap.xml') . "\n";
