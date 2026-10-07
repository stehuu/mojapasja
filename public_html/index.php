<?php
require __DIR__ . '/app/bootstrap.php';

$events = (require __DIR__ . '/app/data/przyjecia.php')['events'];

partial('header', [
    'title'       => '',
    'description' => 'Restauracja Moja Pasja w Sosnowcu-Milowicach: kuchnia polska i europejska, wesela i przyjęcia okolicznościowe dla 10–320 gości. Duży parking, dojazd z S86.',
    'page'        => 'home',
    'path'        => '',
]);
?>

<section class="hero">
    <div class="hero-media" aria-hidden="true">
        <?= picture('hero.jpg', 'Sala restauracji Moja Pasja', 'hero-img', false) ?>
    </div>
    <div class="container hero-content">
        <p class="eyebrow">Sosnowiec · Milowice</p>
        <h1>Gotujemy z pasją.<br>Przyjmujemy jak rodzinę.</h1>
        <p class="lead">Restauracja z kuchnią polską i europejską oraz sale na przyjęcia od 10 do <?= (int) cfg('capacity.max') ?> gości.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= e(url('przyjecia')) ?>">Zaplanuj przyjęcie</a>
            <a class="btn btn-ghost" href="<?= e(url('menu')) ?>">Zobacz menu</a>
        </div>
    </div>
</section>

<section class="section facts" aria-label="Najważniejsze informacje">
    <div class="container facts-grid">
        <div class="fact">
            <p class="fact-value">10–<?= (int) cfg('capacity.max') ?></p>
            <p class="fact-label">gości na przyjęciach</p>
        </div>
        <div class="fact">
            <p class="fact-value">S86</p>
            <p class="fact-label">kilka minut od trasy</p>
        </div>
        <div class="fact">
            <p class="fact-value">Parking</p>
            <p class="fact-label">duży, na miejscu</p>
        </div>
        <?php if (cfg('awards')): ?>
        <div class="fact">
            <p class="fact-value">2023 · 2025</p>
            <p class="fact-label">Mistrzowie Smaku</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section split">
    <div class="container split-grid">
        <div class="split-media">
            <?= picture('o-nas.jpg', 'Kuchnia restauracji Moja Pasja', 'rounded') ?>
        </div>
        <div class="split-text">
            <p class="eyebrow">O nas</p>
            <h2>Zielone Milowice, z dala od zgiełku</h2>
            <p>Moja Pasja to restauracja w spokojnej, zielonej części Sosnowca, przy ulicy Podjazdowej. Z dala od miejskiego hałasu, a jednocześnie z szybkim dojazdem z trasy S86 i dużym parkingiem dla gości.</p>
            <p>Gotujemy kuchnię polską i europejską – na co dzień dla gości restauracji, a od święta dla Waszych najważniejszych uroczystości.</p>
            <a class="link-arrow" href="<?= e(url('kontakt')) ?>">Jak do nas trafić</a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Przyjęcia</p>
            <h2>Każda okazja zasługuje na dobry stół</h2>
        </div>
        <div class="cards">
            <?php foreach ($events as $event): ?>
            <article class="card">
                <?= picture($event['image'], $event['name'], 'card-img') ?>
                <div class="card-body">
                    <h3><?= e($event['name']) ?></h3>
                    <p><?= e($event['text']) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="center"><a class="btn" href="<?= e(url('przyjecia')) ?>">Oferta przyjęć</a></p>
    </div>
</section>

<?php if (cfg('awards')): ?>
<section class="section awards">
    <div class="container awards-inner">
        <p class="eyebrow">Wyróżnienia</p>
        <h2>Laureat konkursu Mistrzowie Smaku</h2>
        <ul class="badges">
            <?php foreach (cfg('awards') as $award): ?>
            <li><?= e($award) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<section class="section cta">
    <div class="container cta-inner">
        <div>
            <h2>Planujesz przyjęcie?</h2>
            <p>Sprawdzimy wolne terminy i umówimy spotkanie w restauracji.</p>
        </div>
        <div class="cta-actions">
            <a class="btn" href="<?= e(url('kontakt#formularz')) ?>">Wyślij zapytanie</a>
            <a class="btn btn-ghost-light" href="<?= e(tel_href(cfg('phones')[1]['number'] ?? cfg('phones')[0]['number'])) ?>"><?= e(cfg('phones')[1]['number'] ?? cfg('phones')[0]['number']) ?></a>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
