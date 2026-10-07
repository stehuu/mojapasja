<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';
date_default_timezone_set('Europe/Warsaw');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function redirect_with_status(string $baseUrl, string $status): void
{
    header('Location: ' . $baseUrl . '?status=' . rawurlencode($status), true, 303);
    exit;
}

function clean_text(string $value, int $maxLength): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function csv_safe(string $value): string
{
    if (preg_match('/^[=+\-@]/u', $value) === 1) {
        return "'" . $value;
    }
    return $value;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

if (!empty($_POST['website'] ?? '')) {
    redirect_with_status($config['redirect_url'], 'success');
}

session_start();
$now = time();
$lastSubmission = (int) ($_SESSION['last_inquiry_submission'] ?? 0);
if ($lastSubmission > 0 && ($now - $lastSubmission) < 20) {
    redirect_with_status($config['redirect_url'], 'error');
}

$name = clean_text((string) ($_POST['name'] ?? ''), 120);
$eventType = clean_text((string) ($_POST['event_type'] ?? ''), 80);
$guestsRaw = filter_var($_POST['guests'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 500],
]);
$guests = $guestsRaw === false ? 0 : (int) $guestsRaw;
$preferredDate = clean_text((string) ($_POST['preferred_date'] ?? ''), 100);
$phone = clean_text((string) ($_POST['phone'] ?? ''), 30);
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim(mb_substr((string) ($_POST['message'] ?? ''), 0, 3000, 'UTF-8'));
$consent = (string) ($_POST['consent'] ?? '');

$allowedEvents = [
    'Wesele',
    'Przyjęcie rodzinne',
    'Chrzciny',
    'Komunia',
    'Jubileusz',
    'Impreza firmowa',
    'Inne',
];

$phoneIsValid = preg_match('/^[0-9+() .\-]{7,30}$/', $phone) === 1;
$emailIsValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false
    && preg_match('/[\r\n]/', $email) !== 1;

if (
    $name === ''
    || !in_array($eventType, $allowedEvents, true)
    || $guests === 0
    || $preferredDate === ''
    || !$phoneIsValid
    || !$emailIsValid
    || $consent !== '1'
) {
    redirect_with_status($config['redirect_url'], 'error');
}

$timestamp = date('Y-m-d H:i:s');
$subject = 'Nowe zapytanie o ofertę — ' . $eventType;
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$body = implode("\r\n", [
    'Nowe zapytanie ze strony Moja Pasja',
    '',
    'Data: ' . $timestamp,
    'Imię i nazwisko: ' . $name,
    'Rodzaj uroczystości: ' . $eventType,
    'Liczba gości: ' . $guests,
    'Preferowany termin: ' . $preferredDate,
    'Telefon: ' . $phone,
    'E-mail: ' . $email,
    '',
    'Dodatkowe informacje:',
    $message !== '' ? $message : '(brak)',
]);

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: Moja Pasja <' . $config['from_email'] . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$mailSent = mail($config['recipient_email'], $encodedSubject, $body, implode("\r\n", $headers));

$storageDirectory = dirname(__DIR__) . '/private_inquiries';
if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0750, true) && !is_dir($storageDirectory)) {
    redirect_with_status($config['redirect_url'], 'error');
}

$ipAddress = clean_text((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 45);
$userAgent = clean_text((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 300);
$csvFile = $storageDirectory . '/zapytania.csv';
$handle = fopen($csvFile, 'ab');

if ($handle === false || !flock($handle, LOCK_EX)) {
    if (is_resource($handle)) {
        fclose($handle);
    }
    redirect_with_status($config['redirect_url'], 'error');
}

$stats = fstat($handle);
if (($stats['size'] ?? 0) === 0) {
    fwrite($handle, "\xEF\xBB\xBF");
    fputcsv($handle, [
        'Data', 'Imię i nazwisko', 'Rodzaj uroczystości', 'Liczba gości',
        'Preferowany termin', 'Telefon', 'E-mail', 'Wiadomość',
        'Zgoda', 'Adres IP', 'Przeglądarka', 'E-mail wysłany',
    ], ';');
}

$row = [
    $timestamp,
    csv_safe($name),
    csv_safe($eventType),
    (string) $guests,
    csv_safe($preferredDate),
    csv_safe($phone),
    csv_safe($email),
    csv_safe($message),
    'TAK',
    $ipAddress,
    csv_safe($userAgent),
    $mailSent ? 'TAK' : 'NIE',
];

$saved = fputcsv($handle, $row, ';') !== false;
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

if (!$saved) {
    redirect_with_status($config['redirect_url'], 'error');
}

$_SESSION['last_inquiry_submission'] = $now;
redirect_with_status($config['redirect_url'], $mailSent ? 'success' : 'saved');
