<?php
/**
 * Karta dań.
 *
 * Kategorie poniżej to wyłącznie szkielet – PRAWDZIWE MENU DO UZUPEŁNIENIA
 * z aktualnej karty restauracji. Nie wpisujemy dań ani cen "na oko".
 *
 * Format pozycji:
 *   ['name' => 'Żurek na zakwasie', 'desc' => 'biała kiełbasa, jajko', 'price' => 22, 'tags' => ['wege']]
 *
 * Dostępne tagi: wege, wegan, ostre, bezglutenowe, polecamy.
 * Kategoria bez pozycji nie jest wyświetlana.
 */

defined('MP_APP') || exit;

return [
    'updated' => null, // np. '2026-10-01' – data ostatniej aktualizacji karty

    'categories' => [
        ['id' => 'przystawki',  'name' => 'Przystawki',      'items' => []],
        ['id' => 'zupy',        'name' => 'Zupy',            'items' => []],
        ['id' => 'dania',       'name' => 'Dania główne',    'items' => []],
        ['id' => 'pierogi',     'name' => 'Pierogi',         'items' => []],
        ['id' => 'makarony',    'name' => 'Makarony',        'items' => []],
        ['id' => 'pizza',       'name' => 'Pizza',           'items' => []],
        ['id' => 'burgery',     'name' => 'Burgery',         'items' => []],
        ['id' => 'dzieci',      'name' => 'Dla dzieci',      'items' => []],
        ['id' => 'desery',      'name' => 'Desery',          'items' => []],
        ['id' => 'napoje',      'name' => 'Napoje',          'items' => []],
    ],
];
