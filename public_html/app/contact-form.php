<?php
/**
 * Obsługa formularza kontaktowego: walidacja, ochrona antyspamowa, wysyłka.
 *
 * Ochrona: token CSRF, ukryte pole-pułapka (honeypot), minimalny czas
 * wypełnienia, limit wysyłek na sesję, blokada wstrzykiwania nagłówków.
 */

defined('MP_APP') || exit;

const CONTACT_MIN_SECONDS = 3;
const CONTACT_MAX_PER_HOUR = 5;

/**
 * @return array{ok: bool, errors: array<string,string>, data: array<string,string>}
 */
function handle_contact_form(array $input, array $eventTypes): array
{
    $data = [
        'name'    => clean_line($input['name'] ?? '', 100),
        'phone'   => clean_line($input['phone'] ?? '', 30),
        'email'   => clean_line($input['email'] ?? '', 150),
        'event'   => clean_line($input['event'] ?? '', 20),
        'date'    => clean_line($input['date'] ?? '', 10),
        'guests'  => clean_line($input['guests'] ?? '', 4),
        'message' => clean_text($input['message'] ?? '', 3000),
        'consent' => !empty($input['consent']) ? '1' : '',
    ];
    $errors = [];

    if (!csrf_valid($input['csrf'] ?? null)) {
        return ['ok' => false, 'errors' => ['_form' => 'Sesja wygasła. Odśwież stronę i spróbuj ponownie.'], 'data' => $data];
    }

    // Boty wypełniają ukryte pole albo wysyłają formularz natychmiast.
    // Udajemy sukces, żeby nie podpowiadać, co je zatrzymało.
    $started = (int) ($_SESSION['form_started'] ?? 0);
    if (!empty($input['website']) || $started === 0 || time() - $started < CONTACT_MIN_SECONDS) {
        return ['ok' => true, 'errors' => [], 'data' => []];
    }

    $recent = array_filter($_SESSION['contact_sent'] ?? [], fn ($t) => $t > time() - 3600);
    if (count($recent) >= CONTACT_MAX_PER_HOUR) {
        return ['ok' => false, 'errors' => ['_form' => 'Wysłano już kilka wiadomości. Zadzwoń do nas, jeśli sprawa jest pilna.'], 'data' => $data];
    }

    if (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Podaj imię i nazwisko.';
    }
    if (!preg_match('/^\+?[0-9 ()-]{9,20}$/', $data['phone'])) {
        $errors['phone'] = 'Podaj poprawny numer telefonu.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adres e-mail wygląda na niepoprawny.';
    }
    if (!array_key_exists($data['event'], $eventTypes)) {
        $errors['event'] = 'Wybierz rodzaj wydarzenia z listy.';
    }
    if ($data['date'] !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['date']);
        if (!$date || $date->format('Y-m-d') !== $data['date']) {
            $errors['date'] = 'Podaj poprawną datę.';
        } elseif ($date < new DateTimeImmutable('today')) {
            $errors['date'] = 'Data nie może być w przeszłości.';
        }
    }
    if ($data['guests'] !== '' && (!ctype_digit($data['guests']) || (int) $data['guests'] < 1 || (int) $data['guests'] > 1000)) {
        $errors['guests'] = 'Podaj liczbę gości.';
    }
    if (mb_strlen($data['message']) < 5) {
        $errors['message'] = 'Napisz kilka słów o swoim zapytaniu.';
    }
    if ($data['consent'] !== '1') {
        $errors['consent'] = 'Zgoda jest potrzebna, abyśmy mogli odpowiedzieć.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'data' => $data];
    }

    if (!send_contact_mail($data, $eventTypes)) {
        error_log('[kontakt] mail() zwróciło false');
        return ['ok' => false, 'errors' => ['_form' => 'Nie udało się wysłać wiadomości. Zadzwoń do nas: ' . cfg('phones')[0]['number'] . '.'], 'data' => $data];
    }

    $recent[] = time();
    $_SESSION['contact_sent'] = array_values($recent);
    unset($_SESSION['form_started']);

    return ['ok' => true, 'errors' => [], 'data' => []];
}

function send_contact_mail(array $data, array $eventTypes): bool
{
    $eventLabel = $data['event'] !== '' ? $eventTypes[$data['event']] : '—';
    $subject = 'Zapytanie ze strony: ' . $eventLabel . ' – ' . $data['name'];

    $body = implode("\n", [
        'Nowe zapytanie z formularza na stronie ' . cfg('base_url'),
        '',
        'Imię i nazwisko: ' . $data['name'],
        'Telefon:         ' . $data['phone'],
        'E-mail:          ' . ($data['email'] ?: '—'),
        'Wydarzenie:      ' . $eventLabel,
        'Data:            ' . ($data['date'] ?: '—'),
        'Liczba gości:    ' . ($data['guests'] ?: '—'),
        '',
        'Wiadomość:',
        $data['message'],
        '',
        '---',
        'Wysłano: ' . date('Y-m-d H:i'),
        'Zgoda na przetwarzanie danych: tak',
    ]);

    $headers = [
        'From'                      => encode_header(cfg('short_name') . ' – formularz') . ' <' . cfg('form_sender') . '>',
        'MIME-Version'              => '1.0',
        'Content-Type'              => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
    ];
    if ($data['email'] !== '') {
        $headers['Reply-To'] = encode_header($data['name']) . ' <' . $data['email'] . '>';
    }

    return mail(cfg('form_recipient'), encode_header($subject), $body, $headers, '-f' . cfg('form_sender'));
}

/** Jedna linia tekstu: bez znaków nowej linii (ochrona przed wstrzyknięciem nagłówków). */
function clean_line(string $value, int $max): string
{
    $value = preg_replace('/[\r\n\t\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
    return mb_substr(trim($value), 0, $max);
}

function clean_text(string $value, int $max): string
{
    $value = str_replace("\r\n", "\n", $value);
    $value = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]+/u', '', $value) ?? '';
    return mb_substr(trim($value), 0, $max);
}

function encode_header(string $value): string
{
    return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
}
