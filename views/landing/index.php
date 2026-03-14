<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $cards */
/** @var string $search */
/** @var UrlGeneratorInterface $urlGenerator */
?>
<section class="hero-section-modern">
    <div class="hero-overlay-modern"></div>
    <div class="container hero-content-modern">
        <h1 class="hero-title-modern"><?= \App\Service\Lang::t('hero_title_line1') ?><br><?= \App\Service\Lang::t('hero_title_line2') ?></h1>
    </div>
</section>
<section class="search-section-modern py-4">
    <div class="container">
        <form method="get" action="/" class="d-flex gap-3 flex-wrap align-items-center justify-content-center mb-5 mt-2">
            <div class="input-group search-group-modern" style="max-width: 600px;">
                <span class="input-group-text bg-white border-0 ps-4 pe-2">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" class="form-control border-0 py-3 ps-2 pe-4" placeholder="<?= \App\Service\Lang::t('search_placeholder') ?>" value="<?= Html::encode($search) ?>" style="box-shadow: none;">
            </div>
            <a href="/" class="btn btn-dusty-blue px-5 py-3"><?= \App\Service\Lang::t('search_all') ?></a>
        </form>
        <h2 class="section-title-modern text-center mb-5"><?= \App\Service\Lang::t('section_partners') ?></h2>
        <div class="row g-4">
            <?php foreach ($cards as $card): ?>
                <?php $imgUrl = !empty($card['file_path']) ? '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $card['file_path']))) : null; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-partner-modern h-100 border-0 shadow-sm">
                        <div class="card-top-dusty bg-dusty-blue position-relative">
                            <div class="d-flex align-items-center h-100 px-4 py-3">
                                <?php if ($imgUrl): ?>
                                    <div class="partner-logo-box bg-white me-3 d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden">
                                        <img src="<?= Html::encode($imgUrl) ?>" alt="Логотип партнера" class="img-fluid" style="object-fit: contain; width: 100%; height: 100%;">
                                    </div>
                                <?php else: ?>
                                    <div class="partner-logo-box bg-white me-3 d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="bi bi-building text-dusty-blue fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <h3 class="partner-name text-white m-0 fw-bold lh-sm name-clamped"><?= Html::encode(\App\Service\Lang::field($card, 'org_name') ?: 'Name') ?></h3>
                            </div>
                        </div>
                        <div class="card-body bg-white p-4 d-flex flex-column">
                            <p class="card-text text-muted mb-4 description-clamped flex-grow-1 text-center">
                                <?php $desc = trim(\App\Service\Lang::field($card, 'description')); ?>
                                <?= empty($desc) ? \App\Service\Lang::t('card_no_description') : Html::encode($desc) ?>
                            </p>
                            <hr class="text-black-50 opacity-25 m-0 mb-3">
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <a href="<?= $urlGenerator->generate('card-view', ['id' => $card['id']]) ?>" class="btn-more text-decoration-none text-muted"><?= \App\Service\Lang::t('card_more') ?> &rarr;</a>
                                <span class="text-muted small"><?= \App\Service\Lang::t('card_since') ?> <?= date('Y', strtotime($card['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($cards)): ?>
            <p class="text-center text-muted py-5"><?= \App\Service\Lang::t('no_cards') ?></p>
        <?php endif; ?>
    </div>
</section>
