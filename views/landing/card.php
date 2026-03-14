<?php

declare(strict_types=1);

use App\Model\Partnership;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $card */
/** @var UrlGeneratorInterface $urlGenerator */

// --- Header ---
$header = [
    'title' => \App\Service\Lang::field($card, 'org_name'),
    'description' => \App\Service\Lang::field($card, 'description'),
    'image' => $card['file_path'] ?? null,
];

// --- Тип организации ---
$orgTypes = [
    'company' => \App\Service\Lang::t('org_type_company'),
    'university' => \App\Service\Lang::t('org_type_university'),
    'research' => \App\Service\Lang::t('org_type_research'),
    'government' => \App\Service\Lang::t('org_type_government'),
    'ngo' => \App\Service\Lang::t('org_type_ngo'),
];
$orgTypeKey = (string) ($card['org_type'] ?? '');
$orgTypeLabel = $orgTypes[$orgTypeKey] ?? ($orgTypeKey !== '' ? $orgTypeKey : '');

// --- Направления (объединяем cooperation_directions + activity_areas + interaction_format) ---
$coopDirections = Partnership::decodeJson($card['cooperation_directions'] ?? null);
$activityAreas = Partnership::decodeJson($card['activity_areas'] ?? null);
$formatItems = Partnership::decodeJson($card['interaction_format'] ?? null);

$coopOptions = [
    'research' => \App\Service\Lang::t('coop_research'),
    'education' => \App\Service\Lang::t('coop_education'),
    'internships' => \App\Service\Lang::t('coop_internships'),
    'joint_projects' => \App\Service\Lang::t('coop_joint_projects'),
    'commercial' => \App\Service\Lang::t('coop_commercial'),
    'grants' => \App\Service\Lang::t('coop_grants'),
    'exchange' => \App\Service\Lang::t('coop_exchange'),
];
$areaOptions = [
    'it' => \App\Service\Lang::t('area_it'),
    'manufacturing' => \App\Service\Lang::t('area_manufacturing'),
    'energy' => \App\Service\Lang::t('area_energy'),
    'medicine' => \App\Service\Lang::t('area_medicine'),
    'education' => \App\Service\Lang::t('area_education'),
    'agriculture' => \App\Service\Lang::t('area_agriculture'),
    'finance' => \App\Service\Lang::t('area_finance'),
];
$formatOptions = [
    'joint_research' => \App\Service\Lang::t('coop_research'),
    'contract_research' => \App\Service\Lang::t('coop_research'),
    'staff_training' => \App\Service\Lang::t('format_staff_training'),
    'joint_lab' => \App\Service\Lang::t('coop_research'),
    'industrial_projects' => \App\Service\Lang::t('format_industrial_projects'),
    'student_internships' => \App\Service\Lang::t('format_student_internships'),
];

$collaborationLabels = [];
foreach ($coopDirections as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = $coopOptions[$key] ?? $key;
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}
foreach ($activityAreas as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = $areaOptions[$key] ?? $key;
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}
foreach ($formatItems as $item) {
    $key = is_string($item) ? $item : '';
    if ($key !== '') {
        $label = $formatOptions[$key] ?? $key;
        if (!in_array($label, $collaborationLabels, true)) {
            $collaborationLabels[] = $label;
        }
    }
}

// --- Подзадачи ---
$subtasksStr = \App\Service\Lang::get() === 'en' && !empty($card['subtasks_en']) && $card['subtasks_en'] !== '[]' ? $card['subtasks_en'] : ($card['subtasks'] ?? null);
$subtasks = Partnership::decodeJson($subtasksStr);
$subtasks = is_array($subtasks) ? array_values(array_filter($subtasks, fn($v) => $v !== '' && $v !== null)) : [];

// --- Цели ---
$goalsStr = \App\Service\Lang::get() === 'en' && !empty($card['goals_en']) && $card['goals_en'] !== '[]' ? $card['goals_en'] : ($card['goals'] ?? null);
$goals = Partnership::decodeJson($goalsStr);
$goals = is_array($goals) ? array_values(array_filter($goals, fn($v) => $v !== '' && $v !== null)) : [];

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
?>
<div class="project-detail-page">
    <div class="project-detail-container">

        <!-- Ссылка назад -->
        <a href="/" class="project-detail-back"><?= \App\Service\Lang::t('back_all_projects') ?></a>

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
                    <?= $header['description'] !== '' ? nl2br(Html::encode($header['description'])) : \App\Service\Lang::t('no_description') ?>
                </p>

                <?php if (!empty($descImages)): ?>
                    <div class="project-desc-images">
                        <?php foreach ($descImages as $src): ?>
                            <img src="<?= Html::encode($src) ?>" alt="" class="project-desc-img" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========== Сайт организации ========== -->
        <?php if (!empty($card['website'])): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= \App\Service\Lang::t('section_website') ?></h2>
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
                    <h2><?= \App\Service\Lang::t('section_directions') ?></h2>
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

        <!-- ========== Подзадачи проекта ========== -->
        <?php if (!empty($subtasks)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= \App\Service\Lang::t('section_subtasks') ?></h2>
                </div>
                <div class="subtasks-list">
                    <?php foreach ($subtasks as $i => $text): ?>
                        <div class="subtask-row">
                            <div class="subtask-num"><?= $i + 1 ?></div>
                            <div class="subtask-text"><?= Html::encode(is_string($text) ? $text : (string) $text) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Цели проекта ========== -->
        <?php if (!empty($goals)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= \App\Service\Lang::t('section_goals') ?></h2>
                </div>
                <div class="goals-list">
                    <?php foreach ($goals as $goal): ?>
                        <div class="goal-row">
                            <div class="goal-icon"></div>
                            <div class="goal-text"><?= Html::encode(is_string($goal) ? $goal : (string) $goal) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ========== Встречи и мероприятия ========== -->
        <?php if (!empty($events)): ?>
            <section class="project-section">
                <div class="project-section-header">
                    <h2><?= \App\Service\Lang::t('section_events') ?></h2>
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
                    <h2><?= \App\Service\Lang::t('section_materials') ?></h2>
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
                    <h2><?= \App\Service\Lang::t('section_contact') ?></h2>
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
                            <p class="contact-method"><?= \App\Service\Lang::t('contact_preferred') ?>: <?= Html::encode($card['contact_method']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </div>
</div>
