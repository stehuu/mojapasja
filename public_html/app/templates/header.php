<?php
/**
 * @var array  $config
 * @var string $title       tytuł podstrony (bez nazwy restauracji)
 * @var string $description meta description
 * @var string $page        identyfikator aktywnej pozycji menu
 * @var string $path        ścieżka podstrony do canonical, np. 'menu'
 */
defined('MP_APP') || exit;

$fullTitle = isset($title) && $title !== '' ? $title . ' | ' . cfg('name') . ' Sosnowiec' : cfg('name') . ' – ' . cfg('tagline');
$description = $description ?? cfg('tagline');
$page = $page ?? '';
$canonical = absolute_url($path ?? '');

$nav = [
    'home'      => ['', 'Start'],
    'menu'      => ['menu', 'Menu'],
    'przyjecia' => ['przyjecia', 'Przyjęcia i wesela'],
    'galeria'   => ['galeria', 'Galeria'],
    'kontakt'   => ['kontakt', 'Kontakt'],
];
$mainPhone = cfg('phones')[0]['number'];
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <?php if (!cfg('indexable')): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($canonical) ?>">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="pl_PL">
    <meta property="og:site_name" content="<?= e(cfg('name')) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php if (is_file(MP_ROOT . '/assets/img/og-image.jpg')): ?>
    <meta property="og:image" content="<?= e(absolute_url('assets/img/og-image.jpg')) ?>">
    <?php endif; ?>

    <meta name="theme-color" content="#5a1a24">
    <link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <?php partial('schema'); ?>
</head>
<body class="page-<?= e($page !== '' ? $page : 'default') ?>">
<a class="skip-link" href="#tresc">Przejdź do treści</a>

<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="logo" href="<?= e(url()) ?>" aria-label="<?= e(cfg('name')) ?> – strona główna">
            <span class="logo-mark" aria-hidden="true">MP</span>
            <span class="logo-text">
                <span class="logo-name">Moja Pasja</span>
                <span class="logo-sub">Restauracja · Sosnowiec</span>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-glowne" data-nav-toggle>
            <span class="nav-toggle-bar" aria-hidden="true"></span>
            <span class="visually-hidden">Menu</span>
        </button>

        <nav class="main-nav" id="menu-glowne" aria-label="Nawigacja główna" data-nav>
            <ul>
                <?php foreach ($nav as $key => [$href, $label]): ?>
                <li><a href="<?= e(url($href)) ?>"<?= $page === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-small header-call" href="<?= e(tel_href($mainPhone)) ?>">Zadzwoń: <?= e($mainPhone) ?></a>
        </nav>
    </div>
</header>

<main id="tresc">
