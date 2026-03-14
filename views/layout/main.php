<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $content */
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isAdmin = (strpos($requestUri, '/admin') === 0);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->getTitle() ?? 'International Partnerships & Projects') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/simple.css">
    <?php if ($isAdmin): ?><link rel="stylesheet" href="/css/admin-form.css"><?php endif; ?>
    <?php $this->head() ?>
</head>
<body class="<?= $isAdmin ? 'admin-layout' : 'landing-layout' ?>">
<?php $this->beginBody() ?>
<header class="site-navbar">
    <div class="container d-flex justify-content-between align-items-center py-3">
        <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
            <img src="/logo/logo_white.png" alt="Kozybayev University" class="navbar-brand-logo" height="40" onerror="this.style.display='none'">
        </a>
        <div class="lang-switch">
            <a href="?lang=ru" class="lang-btn active">RU</a>
            <a href="?lang=en" class="lang-btn">EN</a>
        </div>
    </div>
</header>
<main>
    <?= $content ?>
</main>
<footer class="landing-footer">
    <div class="container text-center py-3">
        <span class="text-muted">©<?= date('Y') ?> Kozybayev University</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/main.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
