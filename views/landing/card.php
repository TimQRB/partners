<?php

declare(strict_types=1);

use App\Model\Partnership;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $card */
/** @var UrlGeneratorInterface $urlGenerator */

// --- Header ---
$header = [
    'title' => (string) ($card['org_name'] ?? ''),
    'description' => (string) ($card['description'] ?? ''),
    'image' => $card['file_path'] ?? null,
];

// --- Тип организации ---
$orgTypes = [
    'company' => 'Компания',
    'university' => 'Университет',
    'research' => 'Исследовательский центр',
    'government' => 'Государственная организация',
    'ngo' => 'НКО',
];
$orgTypeKey = (string) ($card['org_type'] ?? '');
$orgTypeLabel = $orgTypes[$orgTypeKey] ?? ($orgTypeKey !== '' ? $orgTypeKey : '');

// --- Направления (объединяем cooperation_directions + activity_areas + interaction_format) ---
$coopDirections = Partnership::decodeJson($card['cooperation_directions'] ?? null);
$activityAreas = Partnership::decodeJson($card['activity_areas'] ?? null);
$formatItems = Partnership::decodeJson($card['interaction_format'] ?? null);

$coopOptions = [
    'research' => 'Научные исследования',
    'education' => 'Образовательные программы',
    'internships' => 'Стажировки/практика студентов',
    'joint_projects' => 'Совместные проекты',
    'commercial' => 'Коммерческие проекты',
    'grants' => 'Гранты/финансирование',
    'exchange' => 'Обмен студентами или преподавателями',
];
$areaOptions = [
    'it' => 'IT/технологии',
    'manufacturing' => 'Производство',
    'energy' => 'Энергетика',
    'medicine' => 'Медицина',
    'education' => 'Образование',
    'agriculture' => 'Сельское хозяйство',
    'finance' => 'Финансы',
];
$formatOptions = [
    'joint_research' => 'Научные исследования',
    'contract_research' => 'Научные исследования',
    'staff_training' => 'Обучение персонала',
    'joint_lab' => 'Научные исследования',
    'industrial_projects' => 'Промышленные проекты',
    'student_internships' => 'Практика студентов',
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
$subtasks = Partnership::decodeJson($card['subtasks'] ?? null);
$subtasks = is_array($subtasks) ? array_values(array_filter($subtasks, fn($v) => $v !== '' && $v !== null)) : [];

// --- Цели ---
$goals = Partnership::decodeJson($card['goals'] ?? null);
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
        <a href="/" class="project-detail-back">← Все проекты</a>

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
                    <?= $header['description'] !== '' ? nl2br(Html::encode($header['description'])) : 'Нет описания.' ?>
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
                    <h2>Сайт организации</h2>
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
                    <h2>Направления сотрудничества</h2>
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
                    <h2>Подзадачи проекта</h2>
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
                    <h2>Цели проекта</h2>
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
                    <h2>Встречи и мероприятия</h2>
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
                    <h2>Дополнительные материалы</h2>
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
                    <h2>Контактное лицо</h2>
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
                            <p class="contact-method">Предпочитаемый способ связи: <?= Html::encode($card['contact_method']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </div>
</div>
