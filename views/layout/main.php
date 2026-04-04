<?php

declare(strict_types=1);

use App\Service\Lang;
use Yiisoft\Html\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $content */
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isAdmin = (strpos($requestUri, '/admin') === 0);
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
/** Публичная анкета без логина — та же форма, что в админке: нужны admin.css / admin-form.css и разметка как у админа */
$isPublicPartnershipForm = ($path === '/partnerships/create');
$useAdminShell = $isAdmin || $isPublicPartnershipForm;
$isProjectDetail = (preg_match('#^/card/\d+#', $requestUri) === 1);
$isHome = $path === '/';
/** Публичные страницы не главная: тот же навбар, что на главной, нужна подложка и отступ у main */
$needsNavbarUnderlay = !$useAdminShell && !$isHome;
$bodyClass = $useAdminShell ? 'admin-layout' : ($isProjectDetail ? 'landing-layout project-detail-page' : 'landing-layout');
$lang = Lang::get();
parse_str((string) parse_url($requestUri, PHP_URL_QUERY), $langQuery);
$langQueryRu = array_merge($langQuery, ['lang' => 'ru']);
$langQueryEn = array_merge($langQuery, ['lang' => 'en']);
$langQueryKz = array_merge($langQuery, ['lang' => 'kz']);
$urlLangRu = $path . '?' . http_build_query($langQueryRu);
$urlLangEn = $path . '?' . http_build_query($langQueryEn);
$urlLangKz = $path . '?' . http_build_query($langQueryKz);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($lang) ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->getTitle() ?? Lang::t('site_title')) ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/design-system.css">
    <link rel="stylesheet" href="/css/landing.css">
    <?php if ($useAdminShell): ?><link rel="stylesheet" href="/css/admin.css">
        <link rel="stylesheet" href="/css/admin-form.css"><?php endif; ?>
    <?php if ($isProjectDetail): ?><link rel="stylesheet" href="/css/project-detail.css"><?php endif; ?>
    <?php $this->head() ?>
</head>

<body class="<?= Html::encode($bodyClass) ?>">
    <?php $this->beginBody() ?>

    <?php if ($needsNavbarUnderlay): ?>
        <div class="landing-navbar-underlay" aria-hidden="true"></div>
    <?php endif; ?>
    <?php
    $navbarExtra = $useAdminShell ? '' : ($isHome ? 'navbar-home' : 'position-absolute w-100 z-3');
    ?>
    <header class="site-navbar <?= $navbarExtra ?>">
        <div class="container-fluid px-3 px-md-5 d-flex justify-content-between align-items-center py-2">
            <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
                <?php if ($isHome): ?>
                    <img src="/uploads/logo_blue.png" alt="KOZYBAYEV UNIVERSITY" class="navbar-brand-logo" height="50"
                        onerror="this.style.display='none'">
                <?php else: ?>
                    <img src="/uploads/logo_white.png" alt="KOZYBAYEV UNIVERSITY" class="navbar-brand-logo" height="50"
                        onerror="this.style.display='none'">
                <?php endif; ?>
            </a>
            <?php if ($isHome || $isPublicPartnershipForm): ?>
                <div class="lang-switch">
                    <a href="<?= Html::encode($urlLangRu) ?>" class="lang-btn<?= $lang === 'ru' ? ' active' : '' ?>">RU</a>
                    <a href="<?= Html::encode($urlLangEn) ?>" class="lang-btn<?= $lang === 'en' ? ' active' : '' ?>">EN</a>
                    <a href="<?= Html::encode($urlLangKz) ?>" class="lang-btn<?= $lang === 'kz' ? ' active' : '' ?>">KZ</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main<?= $needsNavbarUnderlay ? ' class="landing-main--navbar-offset"' : '' ?>>
        <?= $content ?>
    </main>
    <footer class="landing-footer-modern">
        <div class="container text-center py-4">
            <span class="footer-copy"><?= Html::encode(Lang::t('footer_copy')) ?></span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>