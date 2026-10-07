<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$pages = ['', 'menu', 'przyjecia', 'galeria', 'kontakt', 'polityka-prywatnosci'];

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page):
    $file = __DIR__ . '/' . ($page === '' ? 'index' : $page) . '.php';
?>
    <url>
        <loc><?= e(absolute_url($page)) ?></loc>
        <lastmod><?= date('Y-m-d', filemtime($file)) ?></lastmod>
    </url>
<?php endforeach; ?>
</urlset>
