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
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/simple.css">
    <link rel="stylesheet" href="/css/redesign.css">
    <?php if ($isAdmin): ?><link rel="stylesheet" href="/css/admin-form.css"><?php endif; ?>
    <?php $this->head() ?>
</head>
<body class="<?= $isAdmin ? 'admin-layout' : 'landing-layout' ?>">
<?php $this->beginBody() ?>
<header class="site-navbar <?= !$isAdmin ? 'position-absolute w-100 z-3' : '' ?>">
    <div class="container d-flex justify-content-between align-items-center py-4">
        <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
            <img src="/logo/logo_white.png" alt="KOZYBAYEV UNIVERSITY" class="navbar-brand-logo" height="40" onerror="this.style.display='none'">
            <span class="text-white ms-3 fw-bold fs-5 d-none d-sm-block header-logo-text">KOZYBAYEV UNIVERSITY</span>
        </a>
        <div class="lang-switch-modern">
            <a href="?lang=ru" class="lang-btn-modern active">RU</a>
            <a href="?lang=en" class="lang-btn-modern">EN</a>
        </div>
    </div>
</header>
<main>
    <?= $content ?>
</main>
<footer class="landing-footer-modern">
    <div class="container text-center py-4">
        <span class="footer-copy">© 2026 Kozybayev University</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/main.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
