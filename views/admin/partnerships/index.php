<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $list */
/** @var UrlGeneratorInterface $urlGenerator */
$this->setParameter('pageTitle', 'Блоки');
?>
<div class="admin-blocks-page container py-4">
    <div class="admin-blocks-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="admin-blocks-title mb-0">Блоки</h1>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $urlGenerator->generate('admin/partnerships/create') ?>" class="btn btn-teal">Создать</a>
            <a href="/admin/logout" class="btn btn-admin-outline">Выйти</a>
        </div>
    </div>
    <div class="admin-blocks-card table-responsive">
        <table class="table admin-blocks-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Фото</th>
                    <th>Дата</th>
                    <th class="admin-blocks-actions-col"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $item): ?>
                    <tr>
                        <td class="admin-blocks-name"><?= Html::encode($item['org_name'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($item['file_path'])): ?>
                                <?php $imgUrl = '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $item['file_path']))); ?>
                                <a href="<?= Html::encode($imgUrl) ?>" target="_blank" rel="noopener" class="admin-block-logo-link">
                                    <img src="<?= Html::encode($imgUrl) ?>" alt="" class="admin-block-logo" width="48" height="48">
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-blocks-date"><?= Html::encode($item['created_at'] ?? '') ?></td>
                        <td>
                            <div class="admin-blocks-btns">
                                <a href="<?= $urlGenerator->generate('card-view', ['id' => $item['id']]) ?>" class="btn btn-sm btn-admin-view">Просмотр</a>
                                <a href="<?= $urlGenerator->generate('admin/partnerships/edit', ['id' => $item['id']]) ?>" class="btn btn-sm btn-teal">Изменить</a>
                                <form method="post" action="<?= $urlGenerator->generate('admin/partnerships/delete', ['id' => $item['id']]) ?>" class="d-inline" onsubmit="return confirm('Удалить?');">
                                    <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>
                                    <button type="submit" class="btn btn-sm btn-admin-delete">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($list)): ?>
        <div class="admin-blocks-empty">
            <p class="text-muted mb-0">Нет блоков. Нажмите «Создать».</p>
        </div>
    <?php endif; ?>
</div>
