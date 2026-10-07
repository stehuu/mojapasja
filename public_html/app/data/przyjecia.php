<?php
/**
 * Oferta przyjęć okolicznościowych.
 *
 * Rodzaje przyjęć i usługi dodatkowe zebrane z ogłoszeń restauracji
 * w katalogach weselnych – DO POTWIERDZENIA z właścicielem (co nadal
 * jest w ofercie, czego brakuje).
 */

defined('MP_APP') || exit;

return [
    'events' => [
        [
            'id'    => 'wesela',
            'name'  => 'Wesela',
            'text'  => 'Przyjęcia weselne do 320 gości – od pierwszego spotkania po ostatni toast. Menu, dekoracje i harmonogram ustalamy indywidualnie.',
            'image' => 'przyjecia/wesela.jpg',
        ],
        [
            'id'    => 'komunie',
            'name'  => 'Komunie i chrzciny',
            'text'  => 'Rodzinne uroczystości w kameralnej sali – menu dopasowane do dorosłych i dzieci, z opcją animacji dla najmłodszych.',
            'image' => 'przyjecia/komunie.jpg',
        ],
        [
            'id'    => 'urodziny',
            'name'  => 'Urodziny i jubileusze',
            'text'  => 'Osiemnastki, okrągłe urodziny, rocznice ślubu. Przyjmujemy grupy już od 10 osób.',
            'image' => 'przyjecia/urodziny.jpg',
        ],
        [
            'id'    => 'firmowe',
            'name'  => 'Spotkania firmowe',
            'text'  => 'Bankiety, wigilie i spotkania zespołów. Duży parking i szybki dojazd z S86.',
            'image' => 'przyjecia/firmowe.jpg',
        ],
    ],

    'extras' => [
        'Animator dla dzieci',
        'Obsługa barmańska',
        'Efekty świetlne',
        'Fotobudka',
        'Podświetlany napis LOVE',
        'Słodki stół (kącik deserowy)',
        'Stół wiejski',
    ],

    'steps' => [
        ['Zadzwoń lub wyślij zapytanie', 'Sprawdzimy dostępność terminu i wielkość sali.'],
        ['Spotkanie w restauracji', 'Pokażemy sale, omówimy menu i budżet.'],
        ['Ustalamy szczegóły', 'Dopracujemy menu, dekoracje i harmonogram.'],
        ['Wasz dzień', 'Zajmujemy się resztą – Wy się bawicie.'],
    ],
];
