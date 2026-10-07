/* Restauracja Moja Pasja – drobne usprawnienia. Strona działa także bez JS. */
(function () {
    'use strict';

    // Menu mobilne
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (toggle && nav) {
        var setOpen = function (open) {
            toggle.setAttribute('aria-expanded', String(open));
            nav.classList.toggle('is-open', open);
        };
        toggle.addEventListener('click', function () {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) setOpen(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                setOpen(false);
                toggle.focus();
            }
        });
    }

    // Cień pod nagłówkiem po przewinięciu
    var header = document.querySelector('[data-header]');
    if (header) {
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Mapa Google ładowana dopiero po kliknięciu (prywatność + szybkość)
    document.querySelectorAll('[data-map-load]').forEach(function (button) {
        button.addEventListener('click', function () {
            var box = button.closest('[data-map]');
            var iframe = document.createElement('iframe');
            iframe.src = box.getAttribute('data-src');
            iframe.title = 'Mapa dojazdu do restauracji';
            iframe.loading = 'lazy';
            iframe.referrerPolicy = 'no-referrer-when-downgrade';
            box.replaceChildren(iframe);
            box.classList.add('is-loaded');
        });
    });

    // Prosty podgląd zdjęć w galerii
    var links = document.querySelectorAll('[data-lightbox]');
    if (links.length) {
        var lastFocus = null;
        var close = function (box) {
            box.remove();
            if (lastFocus) lastFocus.focus();
        };
        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                lastFocus = link;
                var box = document.createElement('div');
                box.className = 'lightbox';
                box.setAttribute('role', 'dialog');
                box.setAttribute('aria-modal', 'true');
                var img = document.createElement('img');
                img.src = link.href;
                var thumb = link.querySelector('img');
                img.alt = thumb ? thumb.alt : '';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('aria-label', 'Zamknij');
                btn.textContent = '×';
                box.append(img, btn);
                box.addEventListener('click', function (ev) {
                    if (ev.target !== img) close(box);
                });
                box.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Escape') close(box);
                });
                document.body.appendChild(box);
                btn.focus();
            });
        });
    }
})();
