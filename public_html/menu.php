<?php
require __DIR__ . '/app/bootstrap.php';

$menu = require __DIR__ . '/app/data/menu.php';
$categories = array_values(array_filter($menu['categories'], fn ($c) => !empty($c['items'])));

$tagLabels = [
    'wege'         => 'wege',
    'wegan'        => 'wegańskie',
    'ostre'        => 'ostre',
    'bezglutenowe' => 'bez glutenu',
    'polecamy'     => 'polecamy',
];

partial('header', [
    'title'       => 'Menu',
    'description' => 'Karta dań restauracji Moja Pasja w Sosnowcu – kuchnia polska i europejska.',
    'page'        => 'menu',
    'path'        => 'menu',
]);
?>

<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Karta dań</p>
        <h1>Menu</h1>
        <p class="lead">Kuchnia polska i europejska.</p>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <?php if (!$categories): ?>
        <div class="notice">
            <h2>Aktualizujemy kartę</h2>
            <p>Nowe menu pojawi się tu wkrótce. Zapytaj o dania dnia telefonicznie:
                <a href="<?= e(tel_href(cfg('phones')[0]['number'])) ?>"><?= e(cfg('phones')[0]['number']) ?></a>.</p>
        </div>
        <?php else: ?>
        <nav class="menu-nav" aria-label="Kategorie menu">
            <ul>
                <?php foreach ($categories as $category): ?>
                <li><a href="#<?= e($category['id']) ?>"><?= e($category['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php foreach ($categories as $category): ?>
        <section class="menu-category" id="<?= e($category['id']) ?>">
            <h2><?= e($category['name']) ?></h2>
            <ul class="menu-list">
                <?php foreach ($category['items'] as $item): ?>
                <li class="menu-item">
                    <div class="menu-item-head">
                        <h3><?= e($item['name']) ?></h3>
                        <span class="menu-dots" aria-hidden="true"></span>
                        <span class="menu-price"><?= e(format_price($item['price'] ?? null)) ?></span>
                    </div>
                    <?php if (!empty($item['desc'])): ?>
                    <p><?= e($item['desc']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($item['tags'])): ?>
                    <ul class="tags">
                        <?php foreach ($item['tags'] as $tag): ?>
                        <li class="tag tag-<?= e($tag) ?>"><?= e($tagLabels[$tag] ?? $tag) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endforeach; ?>

        <p class="note">Informacje o alergenach przekaże obsługa.<?php if (!empty($menu['updated'])): ?> Karta aktualna na dzień <?= e(date('d.m.Y', strtotime($menu['updated']))) ?>.<?php endif; ?></p>
        <?php endif; ?>
    </div>
</section>

<?php partial('footer'); ?>
