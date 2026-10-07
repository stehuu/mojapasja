<?php
/** @var array $config */
defined('MP_APP') || exit;
$social = array_filter(cfg('social', []));
$socialLabels = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'google' => 'Opinie Google'];
?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <p class="footer-title"><?= e(cfg('name')) ?></p>
            <address>
                <?= e(cfg('address.street')) ?><br>
                <?= e(cfg('address.postcode')) ?> <?= e(cfg('address.city')) ?>-<?= e(cfg('address.district')) ?>
            </address>
            <p><a href="<?= e(maps_url()) ?>" target="_blank" rel="noopener">Wyznacz trasę →</a></p>
        </div>

        <div>
            <p class="footer-title">Kontakt</p>
            <ul class="plain-list">
                <?php foreach (cfg('phones') as $phone): ?>
                <li><?= e($phone['label']) ?>: <a href="<?= e(tel_href($phone['number'])) ?>"><?= e($phone['number']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></li>
            </ul>
        </div>

        <div>
            <p class="footer-title">Godziny otwarcia</p>
            <?php partial('hours', ['compact' => true]); ?>
        </div>

        <?php if ($social): ?>
        <div>
            <p class="footer-title">Znajdziesz nas</p>
            <ul class="plain-list">
                <?php foreach ($social as $network => $href): ?>
                <li><a href="<?= e($href) ?>" target="_blank" rel="noopener"><?= e($socialLabels[$network] ?? ucfirst($network)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="container footer-bottom">
        <p>© <?= date('Y') ?> <?= e(cfg('name')) ?></p>
        <p><a href="<?= e(url('polityka-prywatnosci')) ?>">Polityka prywatności</a></p>
    </div>
</footer>

<a class="mobile-call" href="<?= e(tel_href(cfg('phones')[0]['number'])) ?>">
    <span aria-hidden="true">☎</span> Zadzwoń
</a>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
