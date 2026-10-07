#!/usr/bin/env python3
"""
Naprawa eksportu strony Moja Pasja (Next.js `output: export`) przed wgraniem na Hostinger.

Użycie:
    python3 -I tools/napraw_eksport.py <public_html.zip | katalog> <katalog_wyjściowy>

Co robi:
  1. Rozpakowuje archiwum, zamieniając "\\" w nazwach na "/" (pliki wgrane z Windowsa
     miały ścieżkę zapisaną w nazwie, np. "images\\foto.jpg" – serwer ich nie widział).
     Przy duplikatach zostaje nowsza wersja.
  2. Usuwa pozostałości starych buildów (inne buildId), nieużywane chunki JS/CSS
     i nieużywane obrazy, archiwa ZIP i domyślne ikony Next.js.
  3. Ustawia osobny tytuł i opis każdej podstronie, dodaje canonical,
     Open Graph i dane strukturalne schema.org.
  4. Tłumaczy stronę 404, zmniejsza logo, generuje robots.txt, sitemap.xml i .htaccess.

Skrypt nie zmienia wyglądu ani treści widocznej na stronach (poza 404).
"""

import io
import json
import re
import shutil
import sys
import zipfile
from datetime import date
from pathlib import Path

DOMAIN = "https://mojapasja.sosnowiec.pl"

OLD_TITLE = "Moja Pasja | Wesela i przyjęcia w Sosnowcu"
OLD_DESC = "Restauracja Moja Pasja w Sosnowcu — trzy sale, ogród, autorska kuchnia i wyjątkowe przyjęcia od 10 do 320 osób."

# katalog strony ("" = główna) -> (tytuł, opis). Bez cudzysłowów i backslashy.
PAGES = {
    "": (
        "Wesela i przyjęcia w Sosnowcu – Restauracja Moja Pasja",
        "Restauracja Moja Pasja w Sosnowcu-Milowicach: trzy sale, ogród i kuchnia przygotowywana na miejscu. Wesela, komunie, chrzciny i imprezy firmowe od 10 do 320 osób.",
    ),
    "o-nas": (
        "O nas – Restauracja Moja Pasja w Sosnowcu, od 2011 roku",
        "Od 2011 roku tworzymy w Sosnowcu przyjęcia z własnym rytmem i rodzinną atmosferą. Poznajcie Restaurację Moja Pasja.",
    ),
    "sale": (
        "Sale weselne i bankietowe w Sosnowcu – Lustrzana, La Perla, Balowa",
        "Trzy sale na przyjęcia od 10 do 320 osób: kameralna Lustrzana, restauracyjna La Perla i Balowa na duże wesela. Restauracja Moja Pasja, Sosnowiec.",
    ),
    "sale/balowa": (
        "Sala Balowa – wesela i duże przyjęcia | Moja Pasja Sosnowiec",
        "Sala Balowa w Restauracji Moja Pasja: przestrzeń na wspólny obiad i długi wieczór na parkiecie. Wesela i duże przyjęcia w Sosnowcu.",
    ),
    "sale/la-perla": (
        "Sala La Perla – obiady i małe uroczystości | Moja Pasja Sosnowiec",
        "Kameralna sala restauracyjna La Perla na obiady rodzinne i mniejsze uroczystości. Restauracja Moja Pasja, Sosnowiec-Milowice.",
    ),
    "sale/lustrzana": (
        "Sala Lustrzana – kameralne przyjęcia | Moja Pasja Sosnowiec",
        "Sala Lustrzana: jasne wnętrze, lustra i wspólny stół. Kameralne przyjęcia rodzinne w Restauracji Moja Pasja w Sosnowcu.",
    ),
    "oferta": (
        "Oferta: wesela, komunie, chrzciny, imprezy firmowe | Moja Pasja",
        "Kompleksowa organizacja przyjęć od 10 do 320 osób w Sosnowcu: wesela, przyjęcia rodzinne, imprezy firmowe i śluby plenerowe. Kuchnia przygotowywana na miejscu.",
    ),
    "galeria": (
        "Galeria – sale i przyjęcia | Restauracja Moja Pasja Sosnowiec",
        "Zdjęcia sal, detali i przyjęć w Restauracji Moja Pasja w Sosnowcu.",
    ),
    "kontakt": (
        "Kontakt i dojazd – Restauracja Moja Pasja, ul. Podjazdowa 21, Sosnowiec",
        "Restauracja Moja Pasja, ul. Podjazdowa 21, 41-203 Sosnowiec. Wesela: 507 136 462, restauracja: 32 307 34 31. Sprawdźcie wolny termin.",
    ),
    "zapytaj-o-oferte": (
        "Zapytaj o ofertę przyjęcia | Restauracja Moja Pasja Sosnowiec",
        "Opowiedzcie nam o planowanej uroczystości – wrócimy z informacją o wolnym terminie i dopasowaną propozycją.",
    ),
}

NOT_FOUND_TITLE = "Nie znaleziono strony | Restauracja Moja Pasja"

SCHEMA = {
    "@context": "https://schema.org",
    "@type": "Restaurant",
    "name": "Restauracja Moja Pasja",
    "url": DOMAIN + "/",
    "logo": DOMAIN + "/logo-moja-pasja.png",
    "image": DOMAIN + "/images/hero-poster.jpg",
    "telephone": "+48 32 307 34 31",
    "email": "biuro@mojapasja.sosnowiec.pl",
    "servesCuisine": ["polska", "europejska"],
    "acceptsReservations": True,
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "ul. Podjazdowa 21",
        "postalCode": "41-203",
        "addressLocality": "Sosnowiec",
        "addressRegion": "śląskie",
        "addressCountry": "PL",
    },
    "contactPoint": [{
        "@type": "ContactPoint",
        "telephone": "+48 507 136 462",
        "contactType": "wesela i przyjęcia",
        "areaServed": "PL",
        "availableLanguage": "Polish",
    }],
}

JUNK_FILES = {"file.svg", "globe.svg", "window.svg", "next.svg", "vercel.svg"}
TEXT_EXT = {".html", ".txt", ".css", ".js", ".json", ".xml", ".webmanifest"}
PRUNE_DIRS = ("_next/static/chunks/", "_next/static/media/", "images/")


def log(msg):
    print(msg)


def safe_name(name):
    name = name.replace("\\", "/").lstrip("/")
    parts = [p for p in name.split("/") if p not in ("", ".")]
    if any(p == ".." for p in parts):
        raise ValueError(f"Niebezpieczna ścieżka w archiwum: {name!r}")
    return "/".join(parts)


def extract(src: Path, out: Path):
    """Rozpakowuje ZIP (albo kopiuje katalog), normalizując "\\" w nazwach."""
    files = {}  # ścieżka -> (data, bajty)
    if src.is_dir():
        for p in src.rglob("*"):
            if p.is_file():
                rel = safe_name(str(p.relative_to(src)))
                files_add(files, rel, p.stat().st_mtime, p.read_bytes())
    else:
        with zipfile.ZipFile(src) as zf:
            for info in zf.infolist():
                if info.is_dir() or info.filename.endswith(("/", "\\")):
                    continue
                rel = safe_name(info.filename)
                if not rel:
                    continue
                files_add(files, rel, info.date_time, zf.read(info))
    for rel, (_, data) in files.items():
        target = out / rel
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_bytes(data)
    return len(files)


def files_add(files, rel, stamp, data):
    if not data and rel.endswith("/"):
        return
    if rel in files and files[rel][0] >= stamp:
        return
    files[rel] = (stamp, data)


def current_build_id(root: Path):
    tree = (root / "__next._tree.txt").read_text(encoding="utf-8")
    return re.search(r'"buildId":"([^"]+)"', tree).group(1)


def remove(path: Path, root: Path, reason: str, removed: list):
    removed.append((str(path.relative_to(root)), reason))
    if path.is_dir():
        shutil.rmtree(path)
    else:
        path.unlink()


def prune(root: Path, build_id: str):
    removed = []
    protected = ("api/", "private_inquiries/")

    for p in sorted(root.rglob("*")):
        if not p.exists() or not p.is_file():
            continue
        rel = str(p.relative_to(root))
        if rel.startswith(protected):
            continue
        if p.suffix.lower() == ".zip":
            remove(p, root, "archiwum ZIP w katalogu publicznym", removed)
        elif rel in JUNK_FILES:
            remove(p, root, "domyślna ikona Next.js", removed)
        elif p.suffix == ".txt":
            m = re.search(r'"buildId":"([^"]+)"', p.read_text(encoding="utf-8", errors="replace"))
            if m and m.group(1) != build_id:
                remove(p, root, f"dane starego buildu {m.group(1)}", removed)

    static = root / "_next" / "static"
    if static.is_dir():
        for d in static.iterdir():
            if d.is_dir() and d.name not in ("chunks", "media", build_id):
                remove(d, root, "manifest starego buildu", removed)

    # Usuwanie nieużywanych chunków i obrazów aż do ustalenia się zbioru.
    while True:
        corpus = "\n".join(
            p.read_text(encoding="utf-8", errors="replace")
            for p in root.rglob("*")
            if p.is_file() and p.suffix.lower() in TEXT_EXT
        )
        unused = [
            p for p in root.rglob("*")
            if p.is_file()
            and (str(p.relative_to(root)).startswith(PRUNE_DIRS)
                 or p.suffix in (".css", ".js") and not str(p.relative_to(root)).startswith(protected))
            and p.name not in corpus
            and not str(p.relative_to(root)).startswith(f"_next/static/{build_id}/")
        ]
        if not unused:
            break
        for p in unused:
            remove(p, root, "nieużywany plik", removed)

    for d in sorted((p for p in root.rglob("*") if p.is_dir()), key=lambda p: -len(p.parts)):
        if not any(d.iterdir()):
            d.rmdir()
    return removed


def page_files(root: Path, page: str):
    d = root / page if page else root
    return [p for p in d.iterdir() if p.is_file() and p.suffix in (".html", ".txt")]


def head_tags(page: str, title: str, desc: str):
    url = f"{DOMAIN}/{page + '/' if page else ''}"
    tags = [
        f'<link rel="canonical" href="{url}"/>',
        '<meta property="og:type" content="website"/>',
        '<meta property="og:locale" content="pl_PL"/>',
        '<meta property="og:site_name" content="Restauracja Moja Pasja"/>',
        f'<meta property="og:title" content="{title}"/>',
        f'<meta property="og:description" content="{desc}"/>',
        f'<meta property="og:url" content="{url}"/>',
        f'<meta property="og:image" content="{DOMAIN}/images/hero-poster.jpg"/>',
        '<meta name="twitter:card" content="summary_large_image"/>',
    ]
    if page == "":
        data = json.dumps(SCHEMA, ensure_ascii=False).replace("</", "<\\/")
        tags.append(f'<script type="application/ld+json">{data}</script>')
    return "".join(tags)


def patch_seo(root: Path):
    for page, (title, desc) in PAGES.items():
        assert '"' not in title + desc and "\\" not in title + desc
        files = page_files(root, page)
        if not files:
            log(f"  ! brak podstrony {page or '/'} – pomijam")
            continue
        for p in files:
            text = p.read_text(encoding="utf-8")
            new = text.replace(OLD_TITLE, title).replace(OLD_DESC, desc)
            if p.name == "index.html" and "</head>" in new:
                new = new.replace("</head>", head_tags(page, title, desc) + "</head>", 1)
            if new != text:
                p.write_text(new, encoding="utf-8")
        log(f"  SEO: /{page + '/' if page else ''} → {title}")


def patch_404(root: Path):
    replacements = {
        "404: This page could not be found.": NOT_FOUND_TITLE,
        "This page could not be found.": "Nie znaleziono strony.",
        OLD_TITLE: NOT_FOUND_TITLE,
    }
    targets = [root / "404.html", *(root / "404").glob("*"), *(root / "_not-found").glob("*")]
    for p in targets:
        if not p.is_file() or p.suffix not in (".html", ".txt"):
            continue
        text = p.read_text(encoding="utf-8")
        new = text
        for a, b in replacements.items():
            new = new.replace(a, b)
        if p.suffix == ".html" and "</head>" in new and 'name="robots"' not in new:
            new = new.replace("</head>", '<meta name="robots" content="noindex"/></head>', 1)
        if new != text:
            p.write_text(new, encoding="utf-8")


def shrink_logo(root: Path):
    logo = root / "logo-moja-pasja.png"
    if not logo.is_file():
        return
    try:
        from PIL import Image
    except ImportError:
        log("  ! brak biblioteki Pillow – logo bez zmian")
        return
    before = logo.stat().st_size
    im = Image.open(logo)
    if im.width <= 600:
        return
    im = im.convert("RGBA")
    im.thumbnail((540, 540), Image.LANCZOS)  # 3× rozmiaru wyświetlania (180×120)
    buf = io.BytesIO()
    im.save(buf, "PNG", optimize=True)
    logo.write_bytes(buf.getvalue())
    log(f"  logo: {before // 1024} KB → {logo.stat().st_size // 1024} KB ({im.width}×{im.height})")


PLACEHOLDER = "images/zdjecie-wkrotce.jpg"
FONT_CANDIDATES = [
    "/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf",
    "/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf",
    "C:/Windows/Fonts/georgia.ttf",
    "/System/Library/Fonts/Supplemental/Georgia.ttf",
]


def make_placeholder(root: Path):
    """Plansza wyświetlana zamiast brakujących zdjęć sal (reguła w .htaccess)."""
    try:
        from PIL import Image, ImageDraw, ImageFont
    except ImportError:
        log("  ! brak biblioteki Pillow – bez planszy zastępczej")
        return
    w, h = 1600, 1066
    img = Image.new("RGB", (w, h), (232, 225, 213))
    logo_path = root / "logo-moja-pasja.png"
    if logo_path.is_file():
        logo = Image.open(logo_path).convert("RGBA")
        logo.thumbnail((420, 420))
        img.paste(logo, ((w - logo.width) // 2, h // 2 - logo.height + 40), logo)
    draw = ImageDraw.Draw(img)
    font = None
    for candidate in FONT_CANDIDATES:
        if Path(candidate).is_file():
            font = ImageFont.truetype(candidate, 54)
            break
    if font:
        text = "Zdjęcia sali wkrótce"
        tw = draw.textlength(text, font=font)
        draw.text(((w - tw) / 2, h // 2 + 90), text, font=font, fill=(110, 86, 52))
    out = root / PLACEHOLDER
    out.parent.mkdir(parents=True, exist_ok=True)
    img.save(out, "JPEG", quality=82, optimize=True, progressive=True)


HTACCESS = r"""# Restauracja Moja Pasja – Hostinger (Apache/LiteSpeed)
# Wygenerowane przez tools/napraw_eksport.py

DirectoryIndex index.html index.php
Options -Indexes
ErrorDocument 404 /404.html
AddDefaultCharset UTF-8

<IfModule mod_rewrite.c>
    RewriteEngine On

    # HTTPS
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Bez www
    RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
    RewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]

    # Domena robocza Hostingera: zakaz indeksowania
    RewriteCond %{HTTP_HOST} \.hostingersite\.com$ [NC]
    RewriteRule ^robots\.txt$ robots-staging.txt [L]
    RewriteCond %{HTTP_HOST} \.hostingersite\.com$ [NC]
    RewriteRule ^ - [E=MP_STAGING:1]

    # Pliki ukryte (poza .well-known) i konfiguracja niedostępne z przeglądarki
    RewriteRule (^|/)\.(?!well-known/) - [F]
    RewriteRule ^api/config\.php$ - [F]

    # Stary adres
    RewriteRule ^index\.php/?$ index.html [L]

    # Brakujące zdjęcia sal → plansza "Zdjęcia sali wkrótce".
    # Wgranie prawdziwego pliku do room-photos/ automatycznie ją zastępuje.
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^room-photos/[^/]+\.(jpe?g|png|webp)$ images/zdjecie-wkrotce.jpg [L]
</IfModule>

<FilesMatch "\.(zip|csv|md|log|bak|sql|py)$">
    Require all denied
</FilesMatch>

<IfModule mod_headers.c>
    Header always set X-Robots-Tag "noindex, nofollow" env=MP_STAGING
    Header always set X-Robots-Tag "noindex, nofollow" env=REDIRECT_MP_STAGING
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"

    # JS i CSS występują tylko w /_next/static/ i mają hash w nazwie – cache na rok
    <FilesMatch "\.(js|css)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
    <FilesMatch "\.(jpe?g|png|webp|avif|svg|mp4|ico)$">
        Header set Cache-Control "public, max-age=2592000"
    </FilesMatch>
    <FilesMatch "\.(html|txt)$">
        Header set Cache-Control "no-cache"
    </FilesMatch>
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript text/javascript image/svg+xml application/xml application/json
</IfModule>
"""


def write_meta_files(root: Path):
    (root / ".htaccess").write_text(HTACCESS, encoding="utf-8")
    (root / "robots.txt").write_text(
        f"User-agent: *\nAllow: /\nDisallow: /api/\n\nSitemap: {DOMAIN}/sitemap.xml\n", encoding="utf-8")
    (root / "robots-staging.txt").write_text(
        "# Domena robocza – nie indeksować\nUser-agent: *\nDisallow: /\n", encoding="utf-8")
    today = date.today().isoformat()
    urls = "".join(
        f"  <url><loc>{DOMAIN}/{page + '/' if page else ''}</loc><lastmod>{today}</lastmod></url>\n"
        for page in PAGES if page_files(root, page)
    )
    (root / "sitemap.xml").write_text(
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' + urls + "</urlset>\n",
        encoding="utf-8")


def report_missing(root: Path):
    corpus = "\n".join(p.read_text(encoding="utf-8", errors="replace")
                       for p in root.rglob("*.html"))
    refs = set(re.findall(r'(?:src|href|poster)="(/[^"?#]+)', corpus))
    refs |= set(re.findall(r'"src\\?":\\?"(/[^"\\]+)', corpus))
    missing = []
    for r in sorted(refs):
        p = root / r.lstrip("/")
        if not (p.is_file() or (p / "index.html").is_file() or r.endswith("/") and p.is_dir()):
            missing.append(r)
    return missing


def main():
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    src, out = Path(sys.argv[1]), Path(sys.argv[2])
    if out.exists():
        shutil.rmtree(out)
    out.mkdir(parents=True)

    n = extract(src, out)
    log(f"Rozpakowano {n} plików do {out}")
    build_id = current_build_id(out)
    log(f"Aktualny build: {build_id}")

    removed = prune(out, build_id)
    log(f"Usunięto {len(removed)} zbędnych plików:")
    for rel, reason in removed:
        log(f"  - {rel}  ({reason})")

    patch_seo(out)
    patch_404(out)
    shrink_logo(out)
    make_placeholder(out)
    write_meta_files(out)

    missing = report_missing(out)
    if missing:
        log("\nUWAGA – strona odwołuje się do plików, których nie ma w archiwum")
        log("(zdjęcia sal zastępuje plansza z images/zdjecie-wkrotce.jpg):")
        for m in missing:
            log(f"  ✗ {m}")
    log("\nGotowe.")


if __name__ == "__main__":
    main()
