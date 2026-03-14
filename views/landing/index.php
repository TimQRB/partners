<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $cards */
/** @var string $search */
/** @var UrlGeneratorInterface $urlGenerator */
?>
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title">International Partnerships & Projects</h1>
    </div>
</section>
<section class="search-section py-4">
    <div class="container">
        <form method="get" action="/" class="d-flex gap-2 flex-wrap align-items-center justify-content-center mb-4">
            <div class="input-group" style="max-width: 400px;">
                <span class="input-group-text"><span class="icon-search"></span></span>
                <input type="text" name="q" class="form-control" placeholder="Поиск по названию..." value="<?= Html::encode($search) ?>">
            </div>
            <a href="/" class="btn btn-teal">ВСЕ</a>
        </form>
        <h2 class="section-title text-center mb-4">МЕЖДУНАРОДНЫЕ ПАРТНЕРЫ</h2>
        <div class="row g-4">
            <?php foreach ($cards as $card): ?>
                <?php $imgUrl = !empty($card['file_path']) ? '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $card['file_path']))) : null; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-partner h-100">
                        <?php if ($imgUrl): ?>
                            <img src="<?= Html::encode($imgUrl) ?>" alt="" class="card-img-top simple-card-img">
                        <?php endif; ?>
                        <div class="card-header-teal"><?= Html::encode($card['org_name'] ?: 'Название') ?></div>
                        <div class="card-body">
                            <p class="card-text"><?= Html::encode(mb_substr($card['description'] ?? '', 0, 120)) ?><?= mb_strlen($card['description'] ?? '') > 120 ? '…' : '' ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <a href="<?= $urlGenerator->generate('card-view', ['id' => $card['id']]) ?>">Подробнее</a>
                                <span class="text-muted small"><?= date('Y', strtotime($card['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($cards)): ?>
            <p class="text-center text-muted py-5">Нет карточек партнёров.</p>
        <?php endif; ?>
    </div>
</section>
