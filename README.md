# Restauracja Moja Pasja – strona internetowa

Strona restauracji Moja Pasja (Sosnowiec, ul. Podjazdowa 21).
Czysty PHP 8 + HTML + CSS, bez frameworków i bazy danych – pod hosting Hostinger.

Audyt, decyzje techniczne i lista rzeczy do zrobienia: [`docs/AUDYT.md`](docs/AUDYT.md).

## Struktura

```
public_html/                ← zawartość tego katalogu wgrywamy do public_html na Hostingerze
├── index.php               strona główna
├── menu.php                karta dań
├── przyjecia.php           wesela i przyjęcia
├── galeria.php             galeria (czyta zdjęcia z assets/img/galeria/)
├── kontakt.php             kontakt, dojazd, formularz
├── polityka-prywatnosci.php
├── 404.php
├── robots.php, sitemap.php generują /robots.txt i /sitemap.xml
├── .htaccess               HTTPS, ładne adresy, bezpieczeństwo, cache
├── app/                    (zablokowany dla przeglądarki)
│   ├── config.php          ← DANE FIRMY: telefony, e-mail, godziny, social media
│   ├── data/menu.php       ← MENU
│   ├── data/przyjecia.php  ← oferta przyjęć i usługi dodatkowe
│   ├── bootstrap.php       wspólne funkcje
│   ├── contact-form.php    obsługa formularza
│   └── templates/          nagłówek, stopka, godziny, dane strukturalne
└── assets/
    ├── css/style.css
    ├── js/main.js
    └── img/                ← ZDJĘCIA
```

## Jak zmienić treść

- **Telefon, e-mail, godziny otwarcia, linki do social media** → `public_html/app/config.php`
- **Menu** → `public_html/app/data/menu.php` (pusta kategoria się nie wyświetla;
  dopóki menu jest puste, strona pokazuje komunikat „Aktualizujemy kartę”)
- **Oferta przyjęć** → `public_html/app/data/przyjecia.php`

## Zdjęcia

Wgraj pliki pod dokładnie tymi nazwami – pojawią się automatycznie
(zanim ich nie ma, strona pokazuje eleganckie zastępniki):

| Plik | Gdzie | Sugerowany rozmiar |
|---|---|---|
| `assets/img/hero.jpg` | duże zdjęcie na górze strony głównej | 2000×1200 |
| `assets/img/o-nas.jpg` | sekcja „O nas” | 1200×900 |
| `assets/img/przyjecia/wesela.jpg` | | 1200×900 |
| `assets/img/przyjecia/komunie.jpg` | | 1200×900 |
| `assets/img/przyjecia/urodziny.jpg` | | 1200×900 |
| `assets/img/przyjecia/firmowe.jpg` | | 1200×900 |
| `assets/img/og-image.jpg` | podgląd przy udostępnianiu na Facebooku | 1200×630 |
| `assets/img/galeria/*.jpg` | galeria – dowolna liczba; nazwa pliku = opis, np. `sala-glowna.jpg` | 1600 px szer. |

Przed wgraniem zmniejsz zdjęcia (np. [squoosh.app](https://squoosh.app), jakość ~80%) –
plik powinien ważyć do ~300 KB.

## Wdrożenie na Hostinger

1. hPanel → **Pliki → Menedżer plików** → katalog `public_html` domeny.
2. Zrób kopię zapasową obecnej zawartości (pobierz jako ZIP).
3. Wgraj **zawartość** katalogu `public_html/` z repozytorium (razem z ukrytym
   plikiem `.htaccess` i katalogiem `app/`).
4. hPanel → **Zaawansowane → Konfiguracja PHP** → wersja PHP **8.1 lub nowsza**.
5. hPanel → **E-maile** → utwórz skrzynkę ustawioną w `form_sender`
   (domyślnie `formularz@mojapasja.sosnowiec.pl`) i wyślij testowe zapytanie.

Alternatywa: hPanel → **Zaawansowane → Git** – podpięcie tego repozytorium
z automatycznym wdrażaniem. Uwaga: Hostinger wdraża całe repozytorium do wskazanego
katalogu, więc strona wyląduje w `public_html/public_html/` – wtedy trzeba albo
wskazać katalog docelowy domeny na podkatalog, albo zmienić strukturę repo.

### Przełączenie na domenę docelową

W `public_html/app/config.php`:

```php
'base_url'  => 'https://mojapasja.sosnowiec.pl',
'indexable' => true,
```

Następnie zgłoś `https://mojapasja.sosnowiec.pl/sitemap.xml` w Google Search Console.

## Uruchomienie lokalne

```bash
php -S localhost:8000 -t public_html dev/router.php
```

`dev/router.php` naśladuje reguły z `.htaccess` (wbudowany serwer PHP ich nie czyta).
Formularz lokalnie zwróci komunikat o błędzie wysyłki – to normalne, bo na komputerze
nie ma serwera pocztowego.
