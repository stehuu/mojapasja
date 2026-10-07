<?php
/**
 * Dane strukturalne schema.org (Restaurant) – pomagają Google pokazać
 * adres, telefon i godziny w wynikach wyszukiwania i w Mapach.
 */
defined('MP_APP') || exit;

$dayMap = [
    'Poniedziałek' => 'Monday', 'Wtorek' => 'Tuesday', 'Środa' => 'Wednesday',
    'Czwartek' => 'Thursday', 'Piątek' => 'Friday', 'Sobota' => 'Saturday', 'Niedziela' => 'Sunday',
];

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Restaurant',
    'name' => cfg('name'),
    'url' => absolute_url(),
    'telephone' => cfg('phones')[0]['number'],
    'email' => cfg('email'),
    'servesCuisine' => ['Polska', 'Europejska'],
    'acceptsReservations' => true,
    'menu' => absolute_url('menu'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => cfg('address.street'),
        'postalCode' => cfg('address.postcode'),
        'addressLocality' => cfg('address.city'),
        'addressRegion' => cfg('address.region'),
        'addressCountry' => cfg('address.country'),
    ],
];

if (cfg('address.lat') !== null && cfg('address.lng') !== null) {
    $schema['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => cfg('address.lat'),
        'longitude' => cfg('address.lng'),
    ];
}

$openingHours = [];
foreach (cfg('hours', []) as $day => $range) {
    if ($range && isset($dayMap[$day])) {
        $openingHours[] = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $dayMap[$day],
            'opens' => $range[0],
            'closes' => $range[1],
        ];
    }
}
if ($openingHours) {
    $schema['openingHoursSpecification'] = $openingHours;
}

$sameAs = array_values(array_filter(cfg('social', [])));
if ($sameAs) {
    $schema['sameAs'] = $sameAs;
}

if (is_file(MP_ROOT . '/assets/img/og-image.jpg')) {
    $schema['image'] = absolute_url('assets/img/og-image.jpg');
}
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
