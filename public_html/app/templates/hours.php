<?php
/**
 * Tabela godzin otwarcia z config.php.
 * @var bool $compact
 */
defined('MP_APP') || exit;
$hours = cfg('hours', []);
?>
<?php if ($hours): ?>
<dl class="hours<?= !empty($compact) ? ' hours-compact' : '' ?>">
    <?php foreach ($hours as $day => $range): ?>
    <div>
        <dt><?= e($day) ?></dt>
        <dd><?= $range ? e($range[0] . '–' . $range[1]) : 'nieczynne' ?></dd>
    </div>
    <?php endforeach; ?>
</dl>
<?php else: ?>
<p>Godziny otwarcia potwierdzimy telefonicznie – <a href="<?= e(tel_href(cfg('phones')[0]['number'])) ?>"><?= e(cfg('phones')[0]['number']) ?></a>.</p>
<?php endif; ?>
<?php if (empty($compact) && cfg('hours_note')): ?>
<p class="note"><?= e(cfg('hours_note')) ?></p>
<?php endif; ?>
