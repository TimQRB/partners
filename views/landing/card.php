<?php

declare(strict_types=1);

use App\Model\Partnership;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array $card */
/** @var UrlGeneratorInterface $urlGenerator */

$coop = Partnership::decodeJson($card['cooperation_directions'] ?? null);
$areas = Partnership::decodeJson($card['activity_areas'] ?? null);
$format = Partnership::decodeJson($card['interaction_format'] ?? null);
$materials = Partnership::decodeJson($card['materials'] ?? null);
$descImages = Partnership::decodeJson($card['description_images'] ?? null);

$orgTypes = [
    'company' => 'Компания',
    'university' => 'Университет',
    'research' => 'Исследовательский центр',
    'government' => 'Государственная организация',
    'ngo' => 'НКО',
];

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
    'joint_research' => 'Совместные исследования',
    'contract_research' => 'Заказные исследования',
    'staff_training' => 'Обучение сотрудников',
    'joint_lab' => 'Совместная лаборатория',
    'industrial_projects' => 'Индустриальные проекты',
    'student_internships' => 'Практика студентов',
];

$imgUrl = null;
if (!empty($card['file_path'])) {
    $imgUrl = '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', (string) $card['file_path'])));
}

$orgTypeKey = (string) ($card['org_type'] ?? '');
$orgTypeLabel = $orgTypes[$orgTypeKey] ?? ($orgTypeKey !== '' ? $orgTypeKey : null);
?>
<div class="card-detail-page container py-4">
    <a href="/" class="card-detail-back">← Все проекты</a>

    <header class="card-detail-hero">
        <div class="card-detail-hero-inner">
            <?php if ($imgUrl): ?>
                <div class="card-detail-hero-logo">
                    <img src="<?= Html::encode($imgUrl) ?>" alt="">
                </div>
            <?php endif; ?>
            <div class="card-detail-hero-text">
                <h1 class="card-detail-hero-title"><?= Html::encode($card['org_name'] ?? '') ?></h1>
                <?php if ($orgTypeLabel): ?>
                    <p class="card-detail-hero-meta"><?= Html::encode($orgTypeLabel) ?></p>
                <?php endif; ?>
                <?php if (!empty($card['country']) || !empty($card['city'])): ?>
                    <p class="card-detail-hero-meta">
                        <?= Html::encode(trim((string) ($card['country'] ?? ''))) ?>
                        <?php if (!empty($card['city'])): ?>, <?= Html::encode($card['city']) ?><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="card-detail-body">
        <?php if (!empty($card['description']) || !empty($descImages)): ?>
            <section class="card-detail-section">
                <h2 class="card-detail-section-title">Описание</h2>
                <div class="card-detail-section-content">
                    <?php if (!empty($card['description'])): ?>
                        <div class="card-detail-description"><?= nl2br(Html::encode($card['description'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($descImages)): ?>
                        <div class="card-detail-description-images">
                            <?php foreach ($descImages as $src): ?>
                                <?php if (!is_string($src) || $src === '') { continue; } ?>
                                <img src="<?= Html::encode($src) ?>" alt="" class="card-detail-desc-img" loading="lazy">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($card['website'])): ?>
            <section class="card-detail-section">
                <h2 class="card-detail-section-title">Сайт организации</h2>
                <div class="card-detail-section-content">
                    <a href="<?= Html::encode($card['website']) ?>" target="_blank" rel="noopener" class="card-detail-link">
                        <?= Html::encode($card['website']) ?>
                    </a>
                </div>
            </section>
        <?php endif; ?>

        <section class="card-detail-section">
            <h2 class="card-detail-section-title">Направления сотрудничества</h2>
            <div class="card-detail-section-content">
                <?php if (!empty($coop)): ?>
                    <ul class="card-detail-list">
                        <?php foreach ($coop as $item): ?>
                            <?php $key = is_string($item) ? $item : ''; if ($key === '') { continue; } ?>
                            <?php $label = $coopOptions[$key] ?? $key; ?>
                            <li><?= Html::encode($label) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="card-detail-muted">—</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card-detail-section">
            <h2 class="card-detail-section-title">Область деятельности</h2>
            <div class="card-detail-section-content">
                <?php if (!empty($areas)): ?>
                    <ul class="card-detail-list">
                        <?php foreach ($areas as $item): ?>
                            <?php $key = is_string($item) ? $item : ''; if ($key === '') { continue; } ?>
                            <?php $label = $areaOptions[$key] ?? $key; ?>
                            <li><?= Html::encode($label) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="card-detail-muted">—</p>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($format)): ?>
            <section class="card-detail-section">
                <h2 class="card-detail-section-title">Возможный формат взаимодействия</h2>
                <div class="card-detail-section-content">
                    <ul class="card-detail-list">
                        <?php foreach ($format as $item): ?>
                            <?php $key = is_string($item) ? $item : ''; if ($key === '') { continue; } ?>
                            <?php $label = $formatOptions[$key] ?? $key; ?>
                            <li><?= Html::encode($label) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($materials)): ?>
            <section class="card-detail-section">
                <h2 class="card-detail-section-title">Дополнительные материалы</h2>
                <div class="card-detail-section-content">
                    <ul class="card-detail-files">
                        <?php foreach ($materials as $path): ?>
                            <?php if (!is_string($path) || $path === '') { continue; } ?>
                            <?php
                            $name = basename(str_replace('\\', '/', $path));
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $iconClass = 'bi-file-earmark';
                            if ($ext === 'pdf') {
                                $iconClass = 'bi-file-pdf';
                            } elseif (in_array($ext, ['doc', 'docx'], true)) {
                                $iconClass = 'bi-file-word';
                            } elseif (in_array($ext, ['ppt', 'pptx'], true)) {
                                $iconClass = 'bi-file-ppt';
                            }
                            ?>
                            <li class="card-detail-file-item">
                                <i class="bi <?= Html::encode($iconClass) ?> card-detail-file-icon" aria-hidden="true"></i>
                                <a href="<?= Html::encode($path) ?>" target="_blank" rel="noopener" download class="card-detail-link"><?= Html::encode($name) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($card['contact_name']) || !empty($card['contact_email']) || !empty($card['contact_phone']) || !empty($card['contact_method'])): ?>
            <section class="card-detail-section card-detail-contact">
                <h2 class="card-detail-section-title">Контакт</h2>
                <div class="card-detail-section-content">
                    <?php if (!empty($card['contact_name'])): ?>
                        <p class="card-detail-contact-name"><?= Html::encode($card['contact_name']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($card['contact_position'])): ?>
                        <p class="card-detail-muted mb-1"><?= Html::encode($card['contact_position']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($card['contact_email'])): ?>
                        <p class="mb-1"><a href="mailto:<?= Html::encode($card['contact_email']) ?>" class="card-detail-link"><?= Html::encode($card['contact_email']) ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($card['contact_phone'])): ?>
                        <p class="mb-1"><?= Html::encode($card['contact_phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($card['contact_method'])): ?>
                        <p class="card-detail-muted mb-0">Предпочитаемый способ связи: <?= Html::encode($card['contact_method']) ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
