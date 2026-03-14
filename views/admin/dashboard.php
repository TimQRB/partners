<?php

declare(strict_types=1);

/** @var \Yiisoft\View\View $this */
/** @var array $stats */
$this->setParameter('pageTitle', 'Админ-панель');
?>
<div class="admin-dashboard">
    <div class="admin-header">
        <h1 class="admin-title">Админ-панель</h1>
        <div class="admin-user-info">
            <a href="/admin/logout" class="admin-logout-btn">Выйти</a>
        </div>
    </div>
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?= (int) ($stats['totalPartnerships'] ?? 0) ?></div>
            <div class="admin-stat-label">Заявок на сотрудничество</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-value"><?= (int) ($stats['totalUsers'] ?? 0) ?></div>
            <div class="admin-stat-label">Пользователей</div>
        </div>
    </div>
    <div class="admin-actions">
        <h2 class="admin-section-title">Быстрые действия</h2>
        <div class="admin-action-buttons">
            <a href="/admin/partnerships" class="admin-action-btn">Заявки на сотрудничество</a>
            <a href="/" class="admin-action-btn">Главная страница</a>
        </div>
    </div>
</div>
