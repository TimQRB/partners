<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var \Yiisoft\View\View $this */
/** @var array $model */
/** @var array $errors */
$this->setParameter('pageTitle', 'Вход в админ-панель');
$errors = $this->getParameter('errors', []);
?>
<div class="admin-login-container">
    <div class="admin-login-box">
        <h1 class="admin-login-title">Вход в админ-панель</h1>
        <?php if (!empty($errors)): ?>
            <div class="admin-form-errors"><?= Html::encode(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/login" class="admin-login-form">
            <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>
            <div class="form-group">
                <label class="form-label">Логин</label>
                <input type="text" name="username" class="form-input" value="<?= Html::encode($model['username'] ?? '') ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-input" autocomplete="current-password">
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" name="rememberMe" id="rememberMe" value="1" <?= !empty($model['rememberMe']) ? 'checked' : '' ?>>
                <label for="rememberMe">Запомнить меня</label>
            </div>
            <div class="form-group">
                <button type="submit" class="admin-login-btn">Войти</button>
            </div>
        </form>
        <div class="admin-login-footer">
            <a href="/" class="admin-back-link">← Вернуться на главную</a>
        </div>
    </div>
</div>
