<?php
require __DIR__ . '/app/bootstrap.php';

$offer = require __DIR__ . '/app/data/przyjecia.php';

partial('header', [
    'title'       => 'Wesela i przyjęcia okolicznościowe',
    'description' => 'Wesela, komunie, chrzciny, urodziny i spotkania firmowe w Sosnowcu. Sale dla 10–320 gości, duży parking, dojazd z S86.',
    'page'        => 'przyjecia',
    'path'        => 'przyjecia',
]);
?>

<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Przyjęcia okolicznościowe</p>
        <h1>Wesela i przyjęcia</h1>
        <p class="lead">Sale dla <?= (int) cfg('capacity.min') ?>–<?= (int) cfg('capacity.max') ?> gości. Wasz pomysł, nasze doświadczenie.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="event-list">
            <?php foreach ($offer['events'] as $i => $event): ?>
            <article class="event<?= $i % 2 ? ' event-reverse' : '' ?>" id="<?= e($event['id']) ?>">
                <div class="event-media"><?= picture($event['image'], $event['name'], 'rounded') ?></div>
                <div class="event-text">
                    <h2><?= e($event['name']) ?></h2>
                    <p><?= e($event['text']) ?></p>
                    <a class="link-arrow" href="<?= e(url('kontakt?wydarzenie=' . $event['id'] . '#formularz')) ?>">Zapytaj o termin</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Dodatki</p>
            <h2>Usługi dodatkowe</h2>
        </div>
        <ul class="extras">
            <?php foreach ($offer['extras'] as $extra): ?>
            <li><?= e($extra) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Krok po kroku</p>
            <h2>Jak przygotowujemy przyjęcie</h2>
        </div>
        <ol class="steps">
            <?php foreach ($offer['steps'] as [$stepTitle, $stepText]): ?>
            <li>
                <h3><?= e($stepTitle) ?></h3>
                <p><?= e($stepText) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
        <p class="note center"><?= e(cfg('meetings_note')) ?></p>
    </div>
</section>

<section class="section cta">
    <div class="container cta-inner">
        <div>
            <h2>Sprawdź wolny termin</h2>
            <p>Odpowiadamy na zapytania najszybciej, jak to możliwe.</p>
        </div>
        <div class="cta-actions">
            <a class="btn" href="<?= e(url('kontakt#formularz')) ?>">Wyślij zapytanie</a>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
