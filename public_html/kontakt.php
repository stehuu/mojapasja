<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/contact-form.php';

start_session();

$eventTypes = [
    ''          => 'Wybierz…',
    'wesela'    => 'Wesele',
    'komunie'   => 'Komunia / chrzciny',
    'urodziny'  => 'Urodziny / jubileusz',
    'firmowe'   => 'Spotkanie firmowe',
    'stolik'    => 'Rezerwacja stolika',
    'inne'      => 'Inne',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handle_contact_form($_POST, $eventTypes);
    if ($result['ok']) {
        flash('contact_status', 'sent');
    } else {
        flash('contact_errors', $result['errors']);
        flash('contact_old', $result['data']);
    }
    header('Location: ' . url('kontakt') . '#formularz', true, 303);
    exit;
}

$status = flash('contact_status');
$errors = flash('contact_errors') ?? [];
$old = flash('contact_old') ?? [];
if (!isset($old['event']) && isset($_GET['wydarzenie']) && isset($eventTypes[$_GET['wydarzenie']])) {
    $old['event'] = $_GET['wydarzenie'];
}
$_SESSION['form_started'] = time();

$val = fn (string $key) => e($old[$key] ?? '');
$err = function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<p class="field-error" id="err-' . e($key) . '">' . e($errors[$key]) . '</p>' : '';
};
$aria = fn (string $key) => isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="err-' . e($key) . '"' : '';

partial('header', [
    'title'       => 'Kontakt i dojazd',
    'description' => 'Kontakt z restauracją Moja Pasja: ' . full_address() . ', tel. ' . cfg('phones')[0]['number'] . '. Zapytaj o wolny termin przyjęcia.',
    'page'        => 'kontakt',
    'path'        => 'kontakt',
]);
?>

<section class="page-hero">
    <div class="container">
        <p class="eyebrow">Kontakt</p>
        <h1>Zapraszamy do Milowic</h1>
        <p class="lead"><?= e(full_address()) ?></p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div class="contact-info">
            <h2>Zadzwoń</h2>
            <ul class="plain-list contact-phones">
                <?php foreach (cfg('phones') as $phone): ?>
                <li>
                    <span><?= e($phone['label']) ?></span>
                    <a href="<?= e(tel_href($phone['number'])) ?>"><?= e($phone['number']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <p>E-mail: <a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></p>
            <p class="note"><?= e(cfg('meetings_note')) ?></p>

            <h2>Godziny otwarcia</h2>
            <?php partial('hours'); ?>

            <h2>Dojazd</h2>
            <p>Spokojna, zielona część Sosnowca – kilka minut od trasy S86. Na miejscu duży parking dla gości.</p>
            <p><a class="btn btn-small" href="<?= e(maps_url()) ?>" target="_blank" rel="noopener">Otwórz w Mapach Google</a></p>

            <div class="map-consent" data-map data-src="https://maps.google.com/maps?q=<?= e(rawurlencode(cfg('name') . ', ' . full_address())) ?>&amp;output=embed">
                <p>Mapa Google zostanie załadowana dopiero po kliknięciu – Google może wtedy zapisać pliki cookie.</p>
                <button class="btn btn-small btn-ghost" type="button" data-map-load>Pokaż mapę</button>
            </div>
        </div>

        <div class="contact-form-wrap" id="formularz">
            <h2>Zapytaj o termin</h2>

            <?php if ($status === 'sent'): ?>
            <div class="alert alert-success" role="status">
                <p><strong>Dziękujemy!</strong> Wiadomość dotarła. Odezwiemy się najszybciej, jak to możliwe.</p>
            </div>
            <?php elseif (isset($errors['_form'])): ?>
            <div class="alert alert-error" role="alert">
                <p><?= e($errors['_form']) ?></p>
            </div>
            <?php elseif ($errors): ?>
            <div class="alert alert-error" role="alert">
                <p>Popraw zaznaczone pola.</p>
            </div>
            <?php endif; ?>

            <form class="form" method="post" action="<?= e(url('kontakt')) ?>#formularz" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="hp" aria-hidden="true">
                    <label for="website">Nie wypełniaj tego pola</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="field">
                    <label for="name">Imię i nazwisko *</label>
                    <input type="text" id="name" name="name" required maxlength="100" autocomplete="name" value="<?= $val('name') ?>"<?= $aria('name') ?>>
                    <?= $err('name') ?>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="phone">Telefon *</label>
                        <input type="tel" id="phone" name="phone" required maxlength="30" autocomplete="tel" value="<?= $val('phone') ?>"<?= $aria('phone') ?>>
                        <?= $err('phone') ?>
                    </div>
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" maxlength="150" autocomplete="email" value="<?= $val('email') ?>"<?= $aria('email') ?>>
                        <?= $err('email') ?>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="event">Rodzaj wydarzenia</label>
                        <select id="event" name="event"<?= $aria('event') ?>>
                            <?php foreach ($eventTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>"<?= ($old['event'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('event') ?>
                    </div>
                    <div class="field">
                        <label for="date">Planowana data</label>
                        <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= $val('date') ?>"<?= $aria('date') ?>>
                        <?= $err('date') ?>
                    </div>
                    <div class="field">
                        <label for="guests">Liczba gości</label>
                        <input type="number" id="guests" name="guests" min="1" max="<?= (int) cfg('capacity.max') ?>" inputmode="numeric" value="<?= $val('guests') ?>"<?= $aria('guests') ?>>
                        <?= $err('guests') ?>
                    </div>
                </div>

                <div class="field">
                    <label for="message">Wiadomość *</label>
                    <textarea id="message" name="message" rows="6" required maxlength="3000"<?= $aria('message') ?>><?= $val('message') ?></textarea>
                    <?= $err('message') ?>
                </div>

                <div class="field field-check">
                    <input type="checkbox" id="consent" name="consent" value="1" required<?= !empty($old['consent']) ? ' checked' : '' ?><?= $aria('consent') ?>>
                    <label for="consent">Wyrażam zgodę na przetwarzanie moich danych w celu odpowiedzi na zapytanie, zgodnie z <a href="<?= e(url('polityka-prywatnosci')) ?>">polityką prywatności</a>. *</label>
                    <?= $err('consent') ?>
                </div>

                <button class="btn" type="submit">Wyślij zapytanie</button>
                <p class="note">* pola wymagane</p>
            </form>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
