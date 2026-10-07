<?php
require __DIR__ . '/app/bootstrap.php';

/*
 * Galeria wczytuje automatycznie wszystkie zdjęcia z assets/img/galeria.
 * Opis (alt) powstaje z nazwy pliku: "sala-glowna-wieczorem.jpg" → "Sala glowna wieczorem".
 * Zdjęcia przed wgraniem zmniejsz do ok. 1600 px szerokości (najlepiej .webp lub .jpg ~80%).
 */
$files = glob(MP_ROOT . '/assets/img/galeria/*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE) ?: [];
sort($files, SORT_NATURAL);

partial('header', [
    'title'       => 'Galeria',
    'description' => 'Zdjęcia sal, dekoracji i dań restauracji Moja Pasja w Sosnowcu.',
    'page'        => 'galeria',
    'path'        => 'galeria',
]);
?>

<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Galeria</p>
        <h1>Zobacz, jak u nas jest</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$files): ?>
        <div class="notice">
            <p>Galeria w przygotowaniu. Zapraszamy na spotkanie – najlepiej zobaczyć sale na żywo.</p>
        </div>
        <?php else: ?>
        <ul class="gallery">
            <?php foreach ($files as $file):
                $name = basename($file);
                $alt = ucfirst(str_replace(['-', '_'], ' ', pathinfo($name, PATHINFO_FILENAME)));
            ?>
            <li>
                <a href="<?= e(asset('img/galeria/' . $name)) ?>" data-lightbox>
                    <?= picture('galeria/' . $name, $alt) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</section>

<?php partial('footer'); ?>
