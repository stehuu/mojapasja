# Audyt i decyzja: budujemy stronę od nowa

Data: 2026-10-07

## 1. Czego NIE udało się sprawdzić (ważne)

Środowisko, w którym pracował asystent, ma ograniczony dostęp do sieci.
**Nie dało się otworzyć ani strony roboczej
`https://darkorange-badger-503455.hostingersite.com/`, ani obecnej strony
`https://mojapasja.sosnowiec.pl/`** – serwer pośredniczący zablokował obie
domeny. Nie widziałem więc ich kodu, wyglądu ani treści.

W związku z tym:

- **nie ma tu oceny obecnej strony opartej na jej oglądaniu** – każda taka
  ocena byłaby zgadywaniem,
- dane firmy (adres, telefony, e-mail, pojemność sal, nagrody, usługi
  dodatkowe) pochodzą z **katalogów firm w wyszukiwarce** (panoramafirm.pl,
  wedding.pl, weselezklasa.pl, gowork.pl, targeo.pl, gdziejemy.pl) i są
  oznaczone w kodzie jako *DO POTWIERDZENIA*,
- **godzin otwarcia nie wpisałem** – katalogi podają sprzeczne wartości
  (np. „pn–czw 10–19, pt–nd całodobowo”, co wygląda na błąd katalogu),
- **menu nie wpisałem** – nie mam aktualnej karty, a zmyślone dania
  i ceny na stronie restauracji to realne ryzyko (klient przychodzi
  na danie, którego nie ma).

Aby audyt obecnej strony był rzetelny, potrzebuję jednego z poniższych:

1. dodania domen `darkorange-badger-503455.hostingersite.com`
   i `mojapasja.sosnowiec.pl` do dozwolonych w ustawieniach sieci
   środowiska (menu środowiska w pasku tytułu sesji → Edit → Network access),
   **albo**
2. wrzucenia do repozytorium plików obecnej strony (np. pobranych przez
   Menedżer plików / FTP w hPanelu) – wtedy przejrzę kod linijka po linijce.

## 2. Co wiadomo na pewno (z danych publicznych)

| Fakt | Źródło | Pewność |
|---|---|---|
| ul. Podjazdowa 21, 41-203 Sosnowiec (Milowice) | panoramafirm, targeo, wedding.pl | wysoka – zgodne w wielu źródłach |
| tel. 32 307 34 31 | targeo, wedding.pl | średnia |
| tel. +48 507 136 462, biuro@mojapasja.sosnowiec.pl | wynik wyszukiwania z opisem z mojapasja.sosnowiec.pl | średnia |
| Sale dla 10–320 osób, wesela do 320 osób | gdziewesele, weselezklasa | średnia |
| Mistrzowie Smaku 2023 i 2025 | opis restauracji w katalogach | średnia – warto mieć skan dyplomu |
| Dawniej „Stara Strzecha” | targeo | średnia – istotne dla SEO (ludzie mogą szukać starej nazwy) |
| Istnieje już domena mojapasja.sosnowiec.pl | wyniki wyszukiwania | wysoka |

## 3. Decyzja techniczna i uzasadnienie

**Rekomenduję zbudowanie strony od nowa** – nie dlatego, że obecna jest na
pewno zła (tego nie wiem), tylko dlatego, że:

1. Nowa wersja i tak powstaje na domenie roboczej, więc nic nie psujemy –
   stara strona działa, dopóki nowa nie będzie gotowa.
2. Strona restauracji ma 5–6 podstron. Napisanie jej czysto od zera
   jest tańsze niż refaktoryzacja kodu, którego nie znamy.
3. Możemy od początku zrobić dobrze rzeczy, które w małych stronach
   restauracji są zwykle zaniedbane (lista niżej).

### Wybrany stos

- **Czysty PHP 8 + HTML + CSS, bez frameworka i bez WordPressa.**
  Hosting Hostinger obsługuje to bez żadnej konfiguracji. Brak wtyczek =
  brak aktualizacji bezpieczeństwa do pilnowania co tydzień.
- **Treść w plikach danych** (`app/config.php`, `app/data/*.php`) –
  zmiana telefonu, godzin czy menu to edycja jednego pliku, bez dotykania
  HTML.
- **Brak zewnętrznych skryptów i czcionek** (Google Fonts, jQuery,
  Bootstrap) – szybciej, bez problemów z RODO.

Świadomy kompromis: bez panelu administracyjnego menu edytuje się
w pliku PHP. Jeśli restauracja chce sama zmieniać menu co tydzień,
kolejnym krokiem powinien być prosty panel (lub edycja menu z arkusza
Google) – to osobna decyzja, nie robiłem tego na zapas.

## 4. Co zostało zrobione w nowej wersji

| Obszar | Rozwiązanie |
|---|---|
| Mobile first | Większość gości restauracji wchodzi z telefonu: responsywny układ, pływający przycisk **„Zadzwoń”**, numery jako linki `tel:` |
| Konwersja | Na każdej stronie droga do telefonu lub formularza zapytania; formularz z rodzajem wydarzenia, datą i liczbą gości |
| SEO lokalne | Dane strukturalne `schema.org/Restaurant` (adres, telefon, godziny), unikalne tytuły i opisy, `canonical`, mapa strony `sitemap.xml`, ładne adresy (`/menu` zamiast `/menu.php`) |
| Domena robocza | `noindex` + `robots.txt` blokujący, żeby Google nie zaindeksował `hostingersite.com` (przełącznik `indexable` w configu) |
| Bezpieczeństwo | Token CSRF, honeypot i limit czasu w formularzu, ochrona przed wstrzyknięciem nagłówków e-mail, blokada katalogu `app/`, nagłówki bezpieczeństwa (CSP, HSTS, X-Frame-Options), wymuszony HTTPS |
| RODO | Mapa Google ładuje się dopiero po kliknięciu, brak cookies analitycznych, wzór polityki prywatności |
| Wydajność | Jeden plik CSS i jeden mały JS, cache plików statycznych na rok z wersjonowaniem, kompresja, leniwe ładowanie zdjęć |
| Dostępność | Semantyczny HTML, „przejdź do treści”, obsługa klawiatury, opisy pól i błędów formularza dla czytników ekranu |
| Brak zdjęć | Strona wygląda poprawnie z eleganckimi zastępnikami; po wgraniu pliku pod właściwą nazwą zdjęcie pojawia się samo |

## 5. Lista rzeczy do zrobienia przed startem (w kolejności)

1. **Potwierdzić dane** w `public_html/app/config.php`: telefony,
   e-mail, godziny otwarcia, NIP do polityki prywatności.
2. **Wpisać menu** do `public_html/app/data/menu.php` (z aktualnej karty).
3. **Zdjęcia** – najważniejsza brakująca rzecz. Lista plików w README.
   Profesjonalna sesja sal i dań da więcej niż jakakolwiek zmiana w kodzie.
4. **Skrzynka `formularz@…`** w hPanelu (Poczta) – nadawca formularza musi
   być w tej samej domenie co strona, inaczej wiadomości trafią do spamu.
   Po przeniesieniu na domenę docelową przetestować wysyłkę.
5. **Spisać adresy starej strony** i dodać przekierowania 301 w
   `.htaccess` (sekcja „Przekierowania ze STAREJ strony”), żeby nie stracić
   pozycji w Google.
6. Podać linki do Facebooka / Instagrama / wizytówki Google (`social`
   w configu).
7. **Polityka prywatności** – do sprawdzenia przez osobę odpowiedzialną
   za RODO (to wzór, nie porada prawna).
8. Po podpięciu domeny docelowej: `base_url` → nowa domena,
   `indexable` → `true`, zgłosić `sitemap.xml` w Google Search Console,
   zaktualizować adres strony w wizytówce Google.

## 6. Ryzyka i otwarte pytania

- **Wizytówka Google jest ważniejsza niż strona.** Dla restauracji
  większość ruchu przychodzi z Map Google. Godziny w wizytówce muszą być
  zgodne z tymi na stronie.
- **Dwie nazwy (Moja Pasja / Stara Strzecha)** – warto zdecydować, czy
  wspominać starą nazwę na stronie („dawniej Stara Strzecha”), żeby
  przechwycić osoby szukające jej w Google.
- **Wysyłka maili przez `mail()`** działa na Hostingerze, ale jest mniej
  niezawodna niż SMTP. Jeśli zapytania będą ginąć, kolejny krok to
  wysyłka przez SMTP (PHPMailer) z kontem pocztowym z hPanelu.
- **Opinie gości** – w katalogach jest wysoka ocena, ale nie wstawiałem
  żadnych cytatów ani liczb, bo nie mogłem ich zweryfikować. Najlepiej
  linkować do wizytówki Google zamiast przepisywać opinie.
