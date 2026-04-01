<?php

declare(strict_types=1);

use App\Model\Partnership;
use App\Service\Lang;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $card */
/** @var UrlGeneratorInterface $urlGenerator */

$knownOrgTypes = ['company', 'university', 'research', 'government', 'ngo'];
$orgTypeKey = (string) ($card['org_type'] ?? '');
$orgTypeLabel = $orgTypeKey !== '' && in_array($orgTypeKey, $knownOrgTypes, true)
    ? Lang::t('org_type_' . $orgTypeKey)
    : $orgTypeKey;

// --- Header ---
$header = [
    'title' => Lang::field($card, 'org_name'),
    'description' => Lang::field($card, 'description'),
    'image' => $card['file_path'] ?? null,
];

// --- Направления (объединяем cooperation_directions + activity_areas + interaction_format) ---
$coopDirections = Partnership::decodeJson($card['cooperation_directions'] ?? null);
$activityAreas = Partnership::decodeJson($card['activity_areas'] ?? null);
$formatItems = Partnership::decodeJson($card['interaction_format'] ?? null);
$coopDirections = is_array($coopDirections) ? $coopDirections : [];
$activityAreas = is_array($activityAreas) ? $activityAreas : [];
$formatItems = is_array($formatItems) ? $formatItems : [];

$collaborationLabels = [];
foreach ($coopDirections as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = Lang::t('coop_' . $key, $key);
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}
foreach ($activityAreas as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = Lang::t('area_' . $key, $key);
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}
foreach ($formatItems as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = Lang::t('format_' . $key, $key);
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}

// --- Действующие проекты (хранятся в subtasks/subtasks_en как массив объектов) ---
$projectsRaw = Lang::jsonField($card, 'subtasks');
$activeProjects = [];
if (is_array($projectsRaw)) {
    foreach ($projectsRaw as $project) {
        if (!is_array($project)) {
            continue;
        }
        $name = trim((string) ($project['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $projImages = [];
        if (is_array($project['images'] ?? null)) {
            foreach ($project['images'] as $im) {
                $im = is_string($im) ? trim($im) : '';
                if ($im !== '' && str_starts_with($im, '/uploads/projects/')) {
                    $projImages[] = $im;
                }
            }
        }
        $activeProjects[] = [
            'name' => $name,
            'description' => trim((string) ($project['description'] ?? '')),
            'goals' => is_array($project['goals'] ?? null) ? array_values(array_filter($project['goals'], fn($v) => trim((string) $v) !== '')) : [],
            'subtasks' => is_array($project['subtasks'] ?? null) ? array_values(array_filter($project['subtasks'], fn($v) => trim((string) $v) !== '')) : [],
            'ready' => trim((string) ($project['ready'] ?? '')),
            'images' => $projImages,
        ];
    }
}

// --- Встречи ---
$events = Partnership::decodeJson($card['events'] ?? null);
$events = is_array($events) ? $events : [];

// --- Описание: изображения ---
$descImages = Partnership::decodeJson($card['description_images'] ?? null);
$descImages = is_array($descImages) ? array_filter($descImages, fn($v) => is_string($v) && $v !== '') : [];

// --- Дополнительные материалы ---
$materials = Partnership::decodeJson($card['materials'] ?? null);
$materials = is_array($materials) ? array_filter($materials, fn($v) => is_string($v) && $v !== '') : [];

// --- Лого ---
$imgUrl = null;
if (!empty($header['image'])) {
    $filePath = (string) $header['image'];
    if (str_starts_with($filePath, '/uploads/')) {
        $imgUrl = $filePath;
    } else {
        $imgUrl = '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $filePath)));
    }
}

$renderProjectRichText = static function (string $text): string {
    if (strpos($text, '<') !== false) {
        $safe = strip_tags($text, '<strong><b><br><img><p><ul><ol><li><em>');
        $safe = preg_replace('/<b\b[^>]*>/i', '<strong>', $safe) ?? $safe;
        $safe = preg_replace('/<\/b>/i', '</strong>', $safe) ?? $safe;
        $safe = preg_replace_callback('/<img[^>]*src=["\']?([^"\'> ]+)["\']?[^>]*>/i', static function (array $m): string {
            $src = trim((string) ($m[1] ?? ''));
            if ($src === '' || !preg_match('#^(https?://|/uploads/)#i', $src)) {
                return '';
            }
            return '<img src="' . Html::encode($src) . '" alt="" class="project-inline-img">';
        }, $safe) ?? $safe;
        return $safe;
    }

    $safe = Html::encode($text);
    $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe) ?? $safe;
    return nl2br($safe);
};
?>
<div class="project-detail-page">
    <div class="project-detail-container">

        <!-- Ссылка назад -->
        <?php
        $backUrl = '/?lang=' . rawurlencode(Lang::get());
        ?>
        <a href="<?= Html::encode($backUrl) ?>" class="project-detail-back"><?= Html::encode(Lang::t('back_all_projects')) ?></a>

        <!-- ========== Главная карточка проекта ========== -->
        <div class="project-card-main">
            <div class="project-card-top">
                <div class="project-card-logo">
                    <?php if ($imgUrl): ?>
                        <img src="<?= Html::encode($imgUrl) ?>" alt="">
                    <?php else: ?>
                        <div class="project-card-logo-placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="project-card-header-text">
                    <h1 class="project-card-title"><?= Html::encode($header['title'] ?: '—') ?></h1>
                    <?php if ($orgTypeLabel !== '' || !empty($card['country']) || !empty($card['city'])): ?>
                        <p class="project-card-meta">
                            <?php if ($orgTypeLabel !== ''): ?>
                                <span><?= Html::encode($orgTypeLabel) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($card['country']) || !empty($card['city'])): ?>
                                <?php if ($orgTypeLabel !== ''): ?> · <?php endif; ?>
                                <span><?= Html::encode(trim(($card['country'] ?? '') . ', ' . ($card['city'] ?? ''), ', ')) ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="project-card-bottom">
                <p class="project-card-description <?= $header['description'] === '' ? 'is-empty' : '' ?>">
                    <?= $header['description'] !== '' ? nl2br(Html::encode($header['description'])) : Html::encode(Lang::t('no_description')) ?>
                </p>

                <?php if (!empty($descImages)): ?>
                    <div class="project-desc-images">
                        <?php foreach ($descImages as $src): ?>
                            <div class="project-desc-img-wrap">
                                <img src="<?= Html::encode($src) ?>" alt="" class="project-desc-img" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========== Действующие проекты (сразу после описания) ========== -->
        <?php if (!empty($activeProjects)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_active_projects')) ?></h2>
                </div>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($activeProjects as $project): ?>
                        <div class="active-project-item">
                            <h3 class="active-project-title"><?= Html::encode($project['name']) ?></h3>
                            <?php if ($project['description'] !== ''): ?>
                                <div class="active-project-description mb-3"><?= $renderProjectRichText($project['description']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($project['images'])): ?>
                                <div class="project-desc-images active-project-images mb-3">
                                    <?php foreach ($project['images'] as $src): ?>
                                        <div class="project-desc-img-wrap">
                                            <img src="<?= Html::encode($src) ?>" alt="" class="project-desc-img" loading="lazy">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['goals'])): ?>
                                <h6 class="mb-2"><?= Html::encode(Lang::t('project_goals')) ?></h6>
                                <ul class="mb-3">
                                    <?php foreach ($project['goals'] as $goal): ?>
                                        <li><?= Html::encode((string) $goal) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($project['subtasks'])): ?>
                                <h6 class="mb-2"><?= Html::encode(Lang::t('project_subtasks')) ?></h6>
                                <ul class="mb-3">
                                    <?php foreach ($project['subtasks'] as $subtask): ?>
                                        <li><?= Html::encode((string) $subtask) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if ($project['ready'] !== ''): ?>
                                <p class="mb-0"><?= Html::encode(Lang::t('project_ready')) ?>: <?= Html::encode($project['ready']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Сайт организации ========== -->
        <?php if (!empty($card['website'])): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_website')) ?></h2>
                </div>
                <a href="<?= Html::encode($card['website']) ?>" target="_blank" rel="noopener" class="project-website-link">
                    <?= Html::encode($card['website']) ?>
                </a>
            </section>
        <?php endif; ?>

        <!-- ========== Направления сотрудничества ========== -->
        <?php if (!empty($collaborationLabels)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_directions')) ?></h2>
                </div>
                <div class="directions-grid">
                    <?php foreach ($collaborationLabels as $label): ?>
                        <div class="direction-chip">
                            <span class="dot"></span>
                            <span><?= Html::encode($label) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Встречи и мероприятия ========== -->
        <?php if (!empty($events)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_events')) ?></h2>
                </div>
                <div class="timeline-list">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $ev = is_array($event) ? $event : [];
                        $date     = $ev['date'] ?? $ev['date_event'] ?? '';
                        $title    = $ev['title'] ?? $ev['name'] ?? '';
                        $location = $ev['location'] ?? $ev['city'] ?? $ev['place'] ?? '';
                        if ($title === '' && $date === '') { continue; }
                        ?>
                        <div class="timeline-event">
                            <span class="timeline-event-dot"></span>
                            <?php if ($date): ?>
                                <p class="timeline-event-date"><?= Html::encode($date) ?></p>
                            <?php endif; ?>
                            <p class="timeline-event-title"><?= Html::encode($title ?: '—') ?></p>
                            <?php if ($location): ?>
                                <p class="timeline-event-location"><?= Html::encode($location) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Дополнительные материалы ========== -->
        <?php if (!empty($materials)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_materials')) ?></h2>
                </div>
                <div class="materials-list">
                    <?php foreach ($materials as $path): ?>
                        <?php
                        $name = basename(str_replace('\\', '/', $path));
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $iconClass = 'bi-file-earmark';
                        if ($ext === 'pdf') { $iconClass = 'bi-file-pdf'; }
                        elseif (in_array($ext, ['doc', 'docx'], true)) { $iconClass = 'bi-file-word'; }
                        elseif (in_array($ext, ['ppt', 'pptx'], true)) { $iconClass = 'bi-file-ppt'; }
                        ?>
                        <a href="<?= Html::encode($path) ?>" target="_blank" rel="noopener" download class="material-item">
                            <i class="bi <?= Html::encode($iconClass) ?>" aria-hidden="true"></i>
                            <span><?= Html::encode($name) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Контактное лицо ========== -->
        <?php if (!empty($card['contact_name']) || !empty($card['contact_email']) || !empty($card['contact_phone'])): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= Html::encode(Lang::t('section_contact')) ?></h2>
                </div>
                <div class="contact-info">
                    <?php if (!empty($card['contact_name'])): ?>
                        <p class="contact-name"><?= Html::encode($card['contact_name']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($card['contact_position'])): ?>
                        <p class="contact-position"><?= Html::encode($card['contact_position']) ?></p>
                    <?php endif; ?>
                    <div class="contact-details">
                        <?php if (!empty($card['contact_email'])): ?>
                            <a href="mailto:<?= Html::encode($card['contact_email']) ?>" class="contact-link">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <?= Html::encode($card['contact_email']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($card['contact_phone'])): ?>
                            <a href="tel:<?= Html::encode($card['contact_phone']) ?>" class="contact-link">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <?= Html::encode($card['contact_phone']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($card['contact_method'])): ?>
                            <p class="contact-method"><?= Html::encode(Lang::t('contact_preferred')) ?>: <?= Html::encode($card['contact_method']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </div>
</div>
