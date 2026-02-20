<?php
/**
 * Partial: Paginación
 * Variables: $pagination (array de paginateQuery()), $baseUrl (string)
 */
if (!isset($pagination) || $pagination['totalPages'] <= 1) return;

$p = $pagination;
$params = $_GET;
unset($params['page']);
$qs = http_build_query($params);
$sep = $qs ? '&' : '';
?>
<div class="pagination-info">
    Mostrando <?= (($p['page'] - 1) * $p['limit']) + 1 ?> –
    <?= min($p['page'] * $p['limit'], $p['total']) ?>
    de <?= $p['total'] ?> registros
</div>
<div class="pagination">
    <?php if ($p['page'] > 1): ?>
        <a href="?<?= $qs . $sep ?>page=<?= $p['page'] - 1 ?>">‹</a>
    <?php else: ?>
        <span class="disabled">‹</span>
    <?php endif; ?>

    <?php
    $start = max(1, $p['page'] - 2);
    $end   = min($p['totalPages'], $p['page'] + 2);
    if ($start > 1) echo '<a href="?' . $qs . $sep . 'page=1">1</a>';
    if ($start > 2) echo '<span class="disabled">…</span>';
    for ($i = $start; $i <= $end; $i++):
    ?>
        <?php if ($i == $p['page']): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="?<?= $qs . $sep ?>page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php
    if ($end < $p['totalPages'] - 1) echo '<span class="disabled">…</span>';
    if ($end < $p['totalPages']) echo '<a href="?' . $qs . $sep . 'page=' . $p['totalPages'] . '">' . $p['totalPages'] . '</a>';
    ?>

    <?php if ($p['page'] < $p['totalPages']): ?>
        <a href="?<?= $qs . $sep ?>page=<?= $p['page'] + 1 ?>">›</a>
    <?php else: ?>
        <span class="disabled">›</span>
    <?php endif; ?>
</div>
