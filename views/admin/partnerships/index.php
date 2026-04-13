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
                    <th class="admin-blocks-priority-col">Приоритет</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th class="admin-blocks-actions-col"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $item): ?>
                    <?php $isPublished = !empty($item['published']); ?>
                    <tr>
                        <td class="admin-blocks-name" title="<?= Html::encode((string) ($item['org_name'] ?? '')) ?>"><?= Html::encode($item['org_name'] ?? '—') ?></td>
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
                        <td class="admin-blocks-priority-cell">
                            <form method="post" action="<?= $urlGenerator->generate('admin/partnerships/priority', ['id' => $item['id']]) ?>" class="d-flex align-items-center gap-2 priority-form">
                                <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>
                                <input type="number" name="priority" class="form-control form-control-sm priority-input" min="0" max="100000" step="1" value="<?= Html::encode((string) ($item['priority'] ?? '0')) ?>" style="max-width: 100px;">
                                <span class="small text-muted priority-save-state"></span>
                            </form>
                        </td>
                        <td class="admin-blocks-date"><?= Html::encode($item['created_at'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= $isPublished ? 'text-bg-success' : 'text-bg-warning' ?>">
                                <?= $isPublished ? 'Опубликовано' : 'На модерации' ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-blocks-btns">
                                <?php if ($isPublished): ?>
                                    <a href="<?= $urlGenerator->generate('card-view', ['id' => $item['id']]) ?>" class="btn btn-sm btn-admin-view">Просмотр</a>
                                <?php else: ?>
                                    <span class="btn btn-sm btn-secondary disabled">Не опубликовано</span>
                                    <form method="post" action="<?= $urlGenerator->generate('admin/partnerships/approve', ['id' => $item['id']]) ?>" class="d-inline">
                                        <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>
                                        <button type="submit" class="btn btn-sm btn-success">Одобрить</button>
                                    </form>
                                <?php endif; ?>
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
<script>
(function () {
    var forms = document.querySelectorAll('.priority-form');
    forms.forEach(function (form) {
        var input = form.querySelector('.priority-input');
        var state = form.querySelector('.priority-save-state');
        if (!input || !state) {
            return;
        }
        var timer = null;
        var lastSaved = String(input.value || '');

        function setState(text, cls) {
            state.textContent = text || '';
            state.className = 'small priority-save-state';
            if (cls) {
                state.classList.add(cls);
            } else {
                state.classList.add('text-muted');
            }
        }

        function normalizeValue() {
            var raw = parseInt(input.value, 10);
            if (Number.isNaN(raw) || raw < 0) {
                raw = 0;
            }
            if (raw > 100000) {
                raw = 100000;
            }
            input.value = String(raw);
        }

        function savePriority() {
            normalizeValue();
            var current = String(input.value || '');
            if (current === lastSaved) {
                setState('', 'text-muted');
                return;
            }
            setState('Сохранение...', 'text-muted');
            var body = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: body,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (res) {
                if (!res.ok) {
                    throw new Error('Save failed');
                }
                lastSaved = current;
                setState('Сохранено', 'text-success');
                setTimeout(function () {
                    if (state.textContent === 'Сохранено') {
                        setState('', 'text-muted');
                    }
                }, 1200);
            }).catch(function () {
                setState('Ошибка сохранения', 'text-danger');
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (timer) {
                clearTimeout(timer);
            }
            savePriority();
        });

        input.addEventListener('input', function () {
            setState('Изменено', 'text-muted');
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(savePriority, 700);
        });

        input.addEventListener('blur', function () {
            if (timer) {
                clearTimeout(timer);
            }
            savePriority();
        });
    });
})();
</script>
