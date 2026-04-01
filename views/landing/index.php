<?php

declare(strict_types=1);

use App\Service\Lang;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $cards */
/** @var string $search */
/** @var UrlGeneratorInterface $urlGenerator */

$searchNormalize = static function (string $s): string {
    $s = trim($s);
    if ($s === '') {
        return '';
    }
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
};
?>
<section class="hero-section-modern">
    <div class="hero-overlay-modern"></div>
    <div class="container hero-content-modern">
        <div class="hero-text-stack">
            <h1 class="hero-title-modern"><?= Html::encode(Lang::t('hero_title_line1')) ?><br><?= Html::encode(Lang::t('hero_title_line2')) ?></h1>
            <a href="<?= Html::encode($urlGenerator->generate('public/partnerships/create')) ?>" class="btn btn-dusty-blue px-4 py-3 hero-cta-btn text-decoration-none">
                <?= Html::encode(Lang::t('hero_cta_form')) ?>
            </a>
        </div>
    </div>
</section>
<section class="search-section-modern py-4">
    <div class="container">
        <form id="partner-search-form" method="get" action="/" class="d-flex gap-3 flex-wrap align-items-center justify-content-center mb-5 mt-2">
            <div class="input-group search-group-modern" style="max-width: 600px;">
                <span class="input-group-text bg-white border-0 ps-4 pe-2">
                    <i class="bi bi-search text-muted" aria-hidden="true"></i>
                </span>
                <input type="search" name="q" id="partner-search-input" class="form-control border-0 py-3 ps-2 pe-4" autocomplete="off" placeholder="<?= Html::encode(Lang::t('search_placeholder')) ?>" value="<?= Html::encode($search) ?>" style="box-shadow: none;" aria-controls="partner-cards-grid">
            </div>
            <button type="button" id="partner-search-reset" class="btn btn-dusty-blue px-5 py-3"><?= Html::encode(Lang::t('search_all')) ?></button>
        </form>
        <h2 class="section-title-modern text-center mb-5"><?= Html::encode(Lang::t('section_partners')) ?></h2>
        <div class="row g-4" id="partner-cards-grid">
            <?php foreach ($cards as $card): ?>
                <?php $imgUrl = !empty($card['file_path']) ? '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $card['file_path']))) : null; ?>
                <?php
                $title = Lang::field($card, 'org_name');
                if ($title === '') {
                    $title = Lang::t('card_title_fallback');
                }
                $desc = trim(Lang::field($card, 'description'));
                $searchBlob = $searchNormalize(implode(' ', array_filter([
                    (string) ($card['org_name'] ?? ''),
                    (string) ($card['org_name_en'] ?? ''),
                    (string) ($card['description'] ?? ''),
                    (string) ($card['description_en'] ?? ''),
                    (string) ($card['country'] ?? ''),
                    (string) ($card['city'] ?? ''),
                ], static fn($v) => $v !== '')));
                ?>
                <?php
                $cardUrlArgs = [
                    'id' => $card['id'],
                    'lang' => Lang::get(),
                ];
                ?>
                <div class="col-md-6 col-lg-4 partner-card-col" data-partner-search="<?= Html::encode($searchBlob) ?>">
                    <div class="card card-partner-modern h-100 border-0 shadow-sm">
                        <div class="card-top-dusty bg-dusty-blue position-relative">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 px-4 py-4 gap-3">
                                <?php if ($imgUrl): ?>
                                    <div class="partner-logo-box bg-white d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden">
                                        <img src="<?= Html::encode($imgUrl) ?>" alt="<?= Html::encode(Lang::t('logo_alt_partner')) ?>" class="img-fluid" style="object-fit: contain; width: 100%; height: 100%; border-radius: 6px;">
                                    </div>
                                <?php else: ?>
                                    <div class="partner-logo-box d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="bi bi-building text-white" style="font-size: 3rem; opacity: 0.8;"></i>
                                    </div>
                                <?php endif; ?>
                                <h3 class="partner-name text-white m-0 fw-bold lh-sm name-clamped text-center"><?= Html::encode($title) ?></h3>
                            </div>
                        </div>
                        <div class="card-body bg-white p-4 d-flex flex-column">
                            <p class="card-text text-black-50 mb-4 description-clamped flex-grow-1">
                                <?= $desc === '' ? Html::encode(Lang::t('card_no_description')) : Html::encode($desc) ?>
                            </p>
                            <hr class="text-black-50 opacity-25 m-0 mb-3">
                            <div class="d-flex align-items-center mt-auto">
                                <a href="<?= $urlGenerator->generate('card-view', $cardUrlArgs) ?>" class="btn-more fw-bold text-black-50 text-decoration-none"><?= Html::encode(Lang::t('card_more')) ?> &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="partner-search-empty" class="text-center text-muted py-5 d-none" role="status"><?= Html::encode(Lang::t('search_no_results')) ?></p>
        <?php if (empty($cards)): ?>
            <p class="text-center text-muted py-5"><?= Html::encode(Lang::t('no_cards')) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php if (!empty($cards)): ?>
<script>
(function () {
    var grid = document.getElementById('partner-cards-grid');
    var input = document.getElementById('partner-search-input');
    var emptyEl = document.getElementById('partner-search-empty');
    var form = document.getElementById('partner-search-form');
    var resetBtn = document.getElementById('partner-search-reset');
    if (!grid || !input) {
        return;
    }
    var cols = grid.querySelectorAll('[data-partner-search]');
    var total = cols.length;
    var t;

    function norm(s) {
        return (s || '').trim().toLowerCase();
    }

    function matches(hay, q) {
        if (!q) {
            return true;
        }
        var parts = q.split(/\s+/).filter(Boolean);
        for (var p = 0; p < parts.length; p++) {
            if (hay.indexOf(parts[p]) === -1) {
                return false;
            }
        }
        return true;
    }

    function apply() {
        var q = norm(input.value);
        var visible = 0;
        for (var i = 0; i < cols.length; i++) {
            var hay = cols[i].getAttribute('data-partner-search') || '';
            var show = matches(hay, q);
            cols[i].classList.toggle('d-none', !show);
            if (show) {
                visible++;
            }
        }
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', visible > 0 || total === 0);
        }
        try {
            var u = new URL(window.location.href);
            var trimmed = input.value.trim();
            if (trimmed) {
                u.searchParams.set('q', trimmed);
            } else {
                u.searchParams.delete('q');
            }
            window.history.replaceState({}, '', u.pathname + u.search + u.hash);
        } catch (e) {}
    }

    function debounced() {
        clearTimeout(t);
        t = setTimeout(apply, 160);
    }

    input.addEventListener('input', debounced);
    input.addEventListener('search', apply);
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            apply();
        });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            input.value = '';
            apply();
            input.focus();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
})();
</script>
<?php endif; ?>
