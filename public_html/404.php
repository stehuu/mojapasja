<?php
require __DIR__ . '/app/bootstrap.php';

http_response_code(404);

partial('header', [
    'title'       => 'Nie znaleziono strony',
    'description' => 'Strona nie istnieje.',
    'page'        => '',
    'path'        => '',
]);
?>

<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Błąd 404</p>
        <h1>Tej strony nie ma w karcie</h1>
        <p class="lead">Mogła zostać przeniesiona po zmianie strony. Sprawdź jedną z poniższych.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= e(url()) ?>">Strona główna</a>
            <a class="btn btn-ghost" href="<?= e(url('menu')) ?>">Menu</a>
            <a class="btn btn-ghost" href="<?= e(url('kontakt')) ?>">Kontakt</a>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
