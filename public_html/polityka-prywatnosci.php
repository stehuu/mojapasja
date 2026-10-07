<?php
require __DIR__ . '/app/bootstrap.php';

partial('header', [
    'title'       => 'Polityka prywatności',
    'description' => 'Zasady przetwarzania danych osobowych na stronie restauracji Moja Pasja.',
    'page'        => '',
    'path'        => 'polityka-prywatnosci',
]);

$company = cfg('company');
?>

<section class="page-hero">
    <div class="container">
        <h1>Polityka prywatności</h1>
    </div>
</section>

<section class="section">
    <div class="container narrow prose">
        <!--
            WZÓR DO WERYFIKACJI PRAWNEJ. Treść nie jest poradą prawną –
            przed publikacją powinien ją sprawdzić prawnik lub osoba
            odpowiedzialna za RODO w firmie.
        -->
        <h2>1. Administrator danych</h2>
        <p>Administratorem danych osobowych jest <?= e($company['legal_name']) ?>, <?= e(full_address()) ?><?= $company['nip'] ? ', NIP ' . e($company['nip']) : '' ?>.
            Kontakt w sprawie danych: <a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a>, tel. <?= e(cfg('phones')[0]['number']) ?>.</p>

        <h2>2. Jakie dane i po co</h2>
        <p>Dane podane w formularzu kontaktowym (imię i nazwisko, telefon, e-mail, informacje o planowanym wydarzeniu) przetwarzamy wyłącznie po to, aby odpowiedzieć na zapytanie i przygotować ofertę – na podstawie Twojej zgody (art. 6 ust. 1 lit. a RODO) oraz działań przed zawarciem umowy (art. 6 ust. 1 lit. b RODO).</p>

        <h2>3. Jak długo</h2>
        <p>Dane przechowujemy przez czas potrzebny do obsługi zapytania, a jeśli dojdzie do zawarcia umowy – przez okres wymagany przepisami prawa (m.in. podatkowymi).</p>

        <h2>4. Komu przekazujemy dane</h2>
        <p>Dane mogą być przetwarzane przez dostawcę hostingu i poczty e-mail, wyłącznie w zakresie niezbędnym do działania strony i skrzynki pocztowej. Nie sprzedajemy danych i nie przekazujemy ich w celach marketingowych.</p>

        <h2>5. Twoje prawa</h2>
        <p>Masz prawo dostępu do swoich danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, przeniesienia oraz cofnięcia zgody w dowolnym momencie (bez wpływu na zgodność z prawem wcześniejszego przetwarzania). Przysługuje Ci też skarga do Prezesa Urzędu Ochrony Danych Osobowych.</p>

        <h2>6. Pliki cookie</h2>
        <p>Strona używa jednego technicznego pliku cookie sesji, niezbędnego do bezpiecznego działania formularza kontaktowego. Nie stosujemy plików cookie analitycznych ani reklamowych. Mapa Google ładuje się dopiero po kliknięciu „Pokaż mapę” – wtedy Google może zapisać własne pliki cookie zgodnie ze swoją polityką prywatności.</p>
    </div>
</section>

<?php partial('footer'); ?>
