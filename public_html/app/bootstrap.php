<?php
/**
 * Wspólny start każdej podstrony: konfiguracja, sesja, nagłówki, helpery.
 */

define('MP_APP', true);
define('MP_ROOT', dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Warsaw');

$config = require __DIR__ . '/config.php';

if (!$config['indexable']) {
    header('X-Robots-Tag: noindex, nofollow');
}

/** Escapowanie do HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cfg(string $key, $default = null)
{
    global $config;
    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Ścieżka względem katalogu głównego strony, np. url('menu'). */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    return rtrim(cfg('base_url'), '/') . url($path);
}

/** Link do pliku statycznego z wersją (cache busting po zmianie pliku). */
function asset(string $path): string
{
    $file = MP_ROOT . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Numer telefonu w formacie dla href="tel:". */
function tel_href(string $number): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $number);
}

function full_address(): string
{
    $a = cfg('address');
    return sprintf('%s, %s %s', $a['street'], $a['postcode'], $a['city']);
}

function maps_url(): string
{
    return 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode(cfg('name') . ', ' . full_address());
}

/**
 * Obrazek, jeśli plik istnieje; w przeciwnym razie estetyczny placeholder.
 * Dzięki temu strona wygląda poprawnie zanim dostaniemy zdjęcia.
 */
function picture(string $path, string $alt, string $class = '', bool $lazy = true): string
{
    $file = MP_ROOT . '/assets/img/' . ltrim($path, '/');
    $classAttr = $class !== '' ? ' class="' . e($class) . '"' : '';

    if (!is_file($file)) {
        return '<div' . ' class="img-placeholder ' . e($class) . '" role="img" aria-label="' . e($alt) . '">'
            . '<span>' . e($alt) . '</span></div>';
    }

    $size = @getimagesize($file);
    $dims = $size ? sprintf(' width="%d" height="%d"', $size[0], $size[1]) : '';
    $loading = $lazy ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"';

    return '<img src="' . e(asset('img/' . ltrim($path, '/'))) . '" alt="' . e($alt) . '"'
        . $dims . $loading . $classAttr . '>';
}

function format_price($price): string
{
    if ($price === null || $price === '') {
        return '';
    }
    return number_format((float) $price, 2, ',', ' ') . ' zł';
}

/** Renderuje szablon z katalogu app/templates. */
function partial(string $name, array $vars = []): void
{
    global $config;
    extract($vars, EXTR_SKIP);
    require __DIR__ . '/templates/' . $name . '.php';
}

/* ---------- Sesja i CSRF (formularz kontaktowy) ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('mp_session');
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    start_session();
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Jednorazowy komunikat między przekierowaniami (wzorzec Post/Redirect/Get). */
function flash(string $key, $value = null)
{
    start_session();
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $stored = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $stored;
}
