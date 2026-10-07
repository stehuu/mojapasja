# Restauracja Moja Pasja – strona internetowa

Strona restauracji Moja Pasja (Sosnowiec, ul. Podjazdowa 21).

`public_html/` to **naprawiona wersja obecnej strony** z serwera Hostinger
(eksport aplikacji Next.js 16 + skrypt PHP formularza). Kodu źródłowego
Next.js w repozytorium nie ma – patrz [`docs/AUDYT.md`](docs/AUDYT.md).

## Struktura

```
public_html/                gotowa strona – zawartość wgrywamy do public_html na Hostingerze
├── index.html, o-nas/, sale/, oferta/, galeria/, kontakt/, zapytaj-o-oferte/
├── _next/                  skrypty i style strony (nie edytować ręcznie)
├── images/                 zdjęcia i wideo
├── room-photos/            ← BRAK – tu trzeba wgrać zdjęcia sal (lista niżej)
├── api/zapytanie.php       obsługa formularza „Zapytaj o ofertę”
├── api/config.php          adres e-mail, na który trafiają zapytania
├── private_inquiries/      kopia zapytań w CSV (zablokowany dla przeglądarki)
├── .htaccess               HTTPS, noindex na domenie roboczej, bezpieczeństwo, cache
├── robots.txt, robots-staging.txt, sitemap.xml
tools/
├── napraw_eksport.py       skrypt, który z ZIP-a z serwera robi public_html/
└── podglad.php             lokalny podgląd strony
docs/
├── AUDYT.md                audyt, znalezione błędy, decyzje, lista zadań
└── materialy/              zrzut wyników „Mistrzowie Smaku”, plansza o dofinansowaniu UE
```

## Brakujące zdjęcia sal – do wgrania

Strona wyświetla te pliki, ale nie było ich na serwerze. Do czasu wgrania
pokazuje się plansza „Zdjęcia sali wkrótce”. Wgraj pliki JPG **dokładnie
pod tymi nazwami** do katalogu `public_html/room-photos/` – plansza zniknie sama.

| Plik | Gdzie się wyświetla |
|---|---|
| `lustrzana-1.jpg` | strona główna, „Sale”, podstrona Sali Lustrzanej |
| `lustrzana-2.jpg`, `lustrzana-4.jpg` | galeria Sali Lustrzanej i podstrona La Perli (jako zdjęcia poglądowe) |
| `lustrzana-3.jpg`, `lustrzana-5.jpg` … `lustrzana-7.jpg` | galeria Sali Lustrzanej |
| `balowa-1.jpg` | strona główna, „Sale”, podstrona Sali Balowej |
| `balowa-2.jpg` | strona główna i „Sale” **jako zdjęcie La Perli**, podstrona La Perli, galeria Balowej |
| `balowa-4.jpg`, `balowa-6.jpg`, `balowa-7.jpg` | galeria Sali Balowej |

Uwaga: La Perla nie ma własnych zdjęć – strona celowo pokazuje zdjęcia
poglądowe z innych sal (z dopiskiem „Galerię La Perla uzupełnimy wkrótce”).
Po zrobieniu zdjęć La Perli trzeba to zmienić w kodzie źródłowym.

Zdjęcia zmniejsz przed wgraniem do ok. 1600 px szerokości (np. [squoosh.app](https://squoosh.app), jakość ~80%).

## Wdrożenie na Hostinger

1. hPanel → **Pliki → Menedżer plików** → `public_html` domeny.
2. **Zrób kopię zapasową** (zaznacz wszystko → Kompresuj → pobierz ZIP).
3. **Pobierz `private_inquiries/zapytania.csv`, jeśli istnieje** – to zapytania klientów.
4. Usuń starą zawartość `public_html` **oprócz katalogu `private_inquiries/`**.
5. Wgraj zawartość katalogu `public_html/` z repozytorium.
   - Najprościej: spakuj go na komputerze do ZIP-a, wgraj ZIP i użyj „Rozpakuj”
     w Menedżerze plików, a potem **usuń ZIP z serwera**.
   - Uwaga na Windows: wbudowane „Wyślij do → folder skompresowany” w starszych
     wersjach zapisuje ścieżki ze znakiem `\`. To właśnie zepsuło obecną stronę.
     Użyj 7-Zip albo sprawdź po rozpakowaniu, że w `images/` są normalne pliki,
     a nie pliki o nazwach `images\…`.
6. Sprawdź, czy na serwerze jest ukryty plik `.htaccess` (w Menedżerze plików
   włącz „Pokaż ukryte pliki”).
7. Wyślij testowe zapytanie z formularza i sprawdź skrzynkę `biuro@`.

### Kontrola po wdrożeniu

```bash
curl -I https://darkorange-badger-503455.hostingersite.com/          # powinno być: X-Robots-Tag: noindex
curl    https://darkorange-badger-503455.hostingersite.com/robots.txt # powinno być: Disallow: /
curl -I https://darkorange-badger-503455.hostingersite.com/images/9b2c560ca7cdeb51514ad5bf048e5233.jpg  # 200
```

Jeśli nagłówka `X-Robots-Tag` nie ma (LiteSpeed może inaczej obsługiwać
warunkowe nagłówki), domenę roboczą i tak chroni `robots.txt`.

### Przejście na domenę docelową

Canonicale, mapa strony i dane strukturalne wskazują już na
`https://mojapasja.sosnowiec.pl`. Po podpięciu tej domeny w Hostingerze
blokada indeksowania wyłącza się sama (działa tylko na `*.hostingersite.com`).
Potem zgłoś `https://mojapasja.sosnowiec.pl/sitemap.xml` w Google Search Console.

Jeśli docelowa domena ma być inna – zmień `DOMAIN` w `tools/napraw_eksport.py`
i uruchom skrypt ponownie.

## Gdy pojawi się nowy eksport strony

Po każdej zmianie w projekcie Next.js:

```bash
python3 -I tools/napraw_eksport.py nowy_eksport.zip public_html
```

Skrypt sam poprawi nazwy plików, usunie stare buildy, ustawi tytuły
podstron, wygeneruje `.htaccess`, `robots.txt` i `sitemap.xml`.
Wymaga Pythona 3 i biblioteki Pillow (`pip install pillow`).

Docelowo lepiej mieć kod źródłowy Next.js w tym repozytorium i poprawki
SEO wpisać bezpośrednio w nim (metadata w plikach `page.tsx`).

## Podgląd lokalny

```bash
php -S localhost:8000 -t public_html tools/podglad.php
```

Formularz lokalnie zapisze zapytanie do CSV, ale nie wyśle maila (brak serwera pocztowego).
