<?php
/**
 * Konfiguracja strony – jedyne miejsce z danymi firmy.
 *
 * Pola oznaczone "DO POTWIERDZENIA" pochodzą z katalogów firm w internecie
 * (panoramafirm.pl, wedding.pl, gowork.pl, targeo.pl) – nie z obecnej strony,
 * której nie dało się pobrać. Przed publikacją zweryfikuj je z właścicielem.
 */

defined('MP_APP') || exit;

return [
    // Adres, pod którym strona będzie działać docelowo (bez końcowego "/").
    // Na czas prac: domena tymczasowa Hostingera.
    'base_url' => 'https://darkorange-badger-503455.hostingersite.com',

    // false = strona NIE jest indeksowana przez Google (noindex).
    // Ustaw true dopiero po podpięciu docelowej domeny, inaczej Google
    // zaindeksuje domenę tymczasową i zrobi duplikat treści.
    'indexable' => false,

    'name'       => 'Restauracja Moja Pasja',
    'short_name' => 'Moja Pasja',
    'tagline'    => 'Restauracja i sale przyjęć w Sosnowcu',

    'address' => [
        'street'   => 'ul. Podjazdowa 21',
        'postcode' => '41-203',
        'city'     => 'Sosnowiec',
        'district' => 'Milowice',
        'region'   => 'śląskie',
        'country'  => 'PL',
        // DO POTWIERDZENIA – współrzędne przybliżone, sprawdź w Google Maps
        // (prawy klik na budynek → skopiuj współrzędne).
        'lat' => null,
        'lng' => null,
    ],

    // DO POTWIERDZENIA – w katalogach występują dwa numery.
    'phones' => [
        ['label' => 'Restauracja', 'number' => '+48 32 307 34 31'],
        ['label' => 'Przyjęcia i wesela', 'number' => '+48 507 136 462'],
    ],
    'email' => 'biuro@mojapasja.sosnowiec.pl',

    // Adres, na który trafiają zapytania z formularza.
    'form_recipient' => 'biuro@mojapasja.sosnowiec.pl',
    // Nadawca wiadomości z formularza – musi być skrzynką w TEJ domenie,
    // na której stoi strona (wymóg SPF/DMARC na Hostingerze).
    'form_sender' => 'formularz@mojapasja.sosnowiec.pl',

    // DO POTWIERDZENIA – katalogi podają sprzeczne godziny.
    // Format: dzień => [otwarcie, zamknięcie] albo null (nieczynne).
    // Puste 'hours' => sekcja godzin się nie wyświetla.
    'hours' => [
        // 'Poniedziałek' => ['12:00', '20:00'],
        // 'Wtorek'       => ['12:00', '20:00'],
        // 'Środa'        => ['12:00', '20:00'],
        // 'Czwartek'     => ['12:00', '20:00'],
        // 'Piątek'       => ['12:00', '22:00'],
        // 'Sobota'       => ['12:00', '22:00'],
        // 'Niedziela'    => ['12:00', '20:00'],
    ],
    'hours_note' => 'W weekendy sale bywają zarezerwowane na przyjęcia zamknięte – przed przyjazdem prosimy o telefon.',

    // Spotkania w sprawie przyjęć (wg wedding.pl: oprócz niedziel i poniedziałków, 8:00–18:00).
    'meetings_note' => 'Spotkania w sprawie przyjęć: wtorek–sobota, 8:00–18:00, po wcześniejszym umówieniu telefonicznym.',

    // Podaj pełne adresy profili; puste wartości są pomijane.
    'social' => [
        'facebook'  => '',
        'instagram' => '',
        'google'    => '', // link do wizytówki Google (opinie)
    ],

    'capacity' => ['min' => 10, 'max' => 320],

    'awards' => [
        'Mistrzowie Smaku 2023',
        'Mistrzowie Smaku 2025',
    ],

    // Dane administratora do polityki prywatności – DO UZUPEŁNIENIA.
    'company' => [
        'legal_name' => 'Agnieszka Stehlik Restauracja „Moja Pasja”',
        'nip'        => '',
    ],
];
