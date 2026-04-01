<?php

declare(strict_types=1);

use App\Model\Partnership;
use App\Service\Lang;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array|null $model */
/** @var array $errors */
/** @var UrlGeneratorInterface $urlGenerator */
/** @var bool $isPublic */
/** @var string|null $createActionRoute */
/** @var string|null $cancelRoute */
$isEdit = $model !== null && isset($model['id']);
$isPublic = isset($isPublic) ? (bool) $isPublic : false;
$createActionRoute = isset($createActionRoute) && is_string($createActionRoute) && $createActionRoute !== ''
    ? $createActionRoute
    : 'admin/partnerships/create-post';
$cancelRoute = isset($cancelRoute) && is_string($cancelRoute) && $cancelRoute !== ''
    ? $cancelRoute
    : 'admin/partnerships';

$formLocale = Lang::get() === 'en' ? 'en' : 'ru';

$displayOrgNameRu = $model ? (string) ($model['org_name'] ?? '') : '';
$displayOrgNameEn = $model ? (string) ($model['org_name_en'] ?? '') : '';
$displayDescriptionRu = $model ? (string) ($model['description'] ?? '') : '';
$displayDescriptionEn = $model ? (string) ($model['description_en'] ?? '') : '';
$subtasksForLocale = $model
    ? Partnership::decodeJson($formLocale === 'en' ? ($model['subtasks_en'] ?? null) : ($model['subtasks'] ?? null))
    : [];
$goalsForLocale = $model
    ? Partnership::decodeJson($formLocale === 'en' ? ($model['goals_en'] ?? null) : ($model['goals'] ?? null))
    : [];

$this->setParameter('pageTitle', $isEdit ? Lang::t('admin_form_edit') : Lang::t('admin_form_new'));
$action = $isEdit
    ? $urlGenerator->generate('admin/partnerships/edit-post', ['id' => $model['id']])
    : $urlGenerator->generate($createActionRoute);
$coopDecoded = $model ? (is_string($model['cooperation_directions'] ?? '') ? json_decode($model['cooperation_directions'], true) : []) : [];
$areasDecoded = $model ? (is_string($model['activity_areas'] ?? '') ? json_decode($model['activity_areas'], true) : []) : [];
$coopDecoded = is_array($coopDecoded) ? $coopDecoded : [];
$areasDecoded = is_array($areasDecoded) ? $areasDecoded : [];

$orgTypes = [];
foreach (['company', 'university', 'research', 'government', 'ngo', 'other'] as $ok) {
    $orgTypes[$ok] = Lang::t('org_type_' . $ok);
}

$coopOptionKeys = ['research', 'education', 'internships', 'joint_projects', 'commercial', 'grants', 'exchange'];
$coopOptions = [];
foreach ($coopOptionKeys as $ck) {
    $coopOptions[$ck] = Lang::t('coop_' . $ck);
}

$areaOptionKeys = ['it', 'manufacturing', 'energy', 'medicine', 'education', 'agriculture', 'finance'];
$areaOptions = [];
foreach ($areaOptionKeys as $ak) {
    $areaOptions[$ak] = Lang::t('area_' . $ak);
}

$formatOptionKeys = ['joint_research', 'contract_research', 'staff_training', 'joint_lab', 'industrial_projects', 'student_internships'];
$formatOptions = [];
foreach ($formatOptionKeys as $fk) {
    $formatOptions[$fk] = Lang::t('format_' . $fk);
}

$adminFormJsI18n = json_encode([
    'remove' => Lang::t('admin_aria_remove'),
    'project_name' => Lang::t('admin_project_name'),
    'project_description' => Lang::t('admin_project_description'),
    'project_goals' => Lang::t('admin_project_goals'),
    'project_subtasks' => Lang::t('admin_project_subtasks'),
    'project_ready' => Lang::t('admin_project_ready'),
    'project_name_ph' => Lang::t('admin_project_name_ph'),
    'project_desc_ph' => Lang::t('admin_project_desc_ph'),
    'project_goals_ph' => Lang::t('admin_project_goals_ph'),
    'project_subtasks_ph' => Lang::t('admin_project_subtasks_ph'),
    'project_ready_ph' => Lang::t('admin_project_ready_ph'),
    'event_date' => Lang::t('admin_event_date'),
    'event_title' => Lang::t('admin_event_title'),
    'event_location' => Lang::t('admin_event_location'),
    'event_date_ph' => Lang::t('admin_event_date_ph'),
    'event_title_ph' => Lang::t('admin_event_title_ph'),
    'event_loc_ph' => Lang::t('admin_event_loc_ph'),
    'choose_files' => Lang::t('admin_choose_files'),
    'no_files_chosen' => Lang::t('admin_no_files_chosen'),
    'files_chosen' => Lang::t('admin_files_chosen'),
    'project_images' => Lang::t('admin_project_images'),
    'project_images_hint' => Lang::t('admin_project_images_hint'),
    'remove_file' => Lang::t('admin_remove_file'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

?>
<div class="admin-form-page">
<div class="container py-4">
    <h1 class="admin-form-title"><?= Html::encode($isEdit ? Lang::t('admin_form_edit') : Lang::t('admin_form_new')) ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= Html::encode(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= Html::encode($action) ?>" enctype="multipart/form-data">
        <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>
        <div id="pending-removals" class="d-none"></div>
        <div class="admin-form-card mb-3">
            <div class="card-body py-2">
                <ul class="nav nav-tabs" id="form-lang-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link form-lang-tab<?= $formLocale === 'ru' ? ' active' : '' ?>" data-lang="ru" type="button" role="tab">RU</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link form-lang-tab<?= $formLocale === 'en' ? ' active' : '' ?>" data-lang="en" type="button" role="tab">EN</button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="admin-form-teal-banner">
                <div class="admin-form-teal-placeholder">
                    <?php if (!empty($model['file_path'])): ?>
                        <?php $imgUrl = '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $model['file_path']))); ?>
                        <img src="<?= Html::encode($imgUrl) ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label d-block small mb-1 text-white"><?= Html::encode(Lang::t('admin_org_name_label')) ?> <span class="text-danger">*</span></label>
                    <div class="lang-field lang-field-ru<?= $formLocale === 'ru' ? '' : ' d-none' ?>">
                        <input type="text" name="org_name_ru" class="form-control bg-white border-0" value="<?= Html::encode($displayOrgNameRu) ?>" placeholder="<?= Html::encode(Lang::t('admin_org_name_ph')) ?>" style="max-width: 100%;">
                    </div>
                    <div class="lang-field lang-field-en<?= $formLocale === 'en' ? '' : ' d-none' ?>">
                        <input type="text" name="org_name_en" class="form-control bg-white border-0" value="<?= Html::encode($displayOrgNameEn) ?>" placeholder="<?= Html::encode(Lang::t('admin_org_name_ph')) ?>" style="max-width: 100%;">
                    </div>
                </div>
                <div class="align-self-end">
                    <label class="form-label small mb-1 d-block text-white"><?= Html::encode(Lang::t('admin_logo')) ?> <span class="opacity-75">*</span></label>
                    <input type="file" name="file" id="logo-file-input" class="d-none" accept="image/*">
                    <button type="button" id="logo-file-select-btn" class="btn btn-sm btn-light"><?= Html::encode(Lang::t('admin_choose_file')) ?></button>
                </div>
            </div>
            <div class="card-body">
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_org_type')) ?> <span class="text-danger">*</span></label>
                    <?php
                    $orgTypeVal = $model['org_type'] ?? '';
                    $orgTypeInList = $orgTypeVal !== '' && isset($orgTypes[$orgTypeVal]);
                    $orgTypeOtherVal = $orgTypeInList ? '' : $orgTypeVal;
                    $orgTypeSelect = $orgTypeInList ? $orgTypeVal : ($orgTypeVal !== '' ? 'other' : '');
                    ?>
                    <select name="org_type" class="form-select" id="org_type_select" required>
                        <option value="" <?= $orgTypeSelect === '' ? 'selected' : '' ?>><?= Html::encode(Lang::t('admin_select')) ?></option>
                        <?php foreach ($orgTypes as $val => $label): ?>
                            <option value="<?= Html::encode($val) ?>" <?= $orgTypeSelect === $val ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="org_type_other" class="form-control mt-1" id="org_type_other" placeholder="<?= Html::encode(Lang::t('admin_org_type_other_ph')) ?>" value="<?= Html::encode($orgTypeOtherVal) ?>" style="display:none">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="admin-form-field">
                            <label class="form-label"><?= Html::encode(Lang::t('admin_country')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="country" class="form-control" value="<?= Html::encode($model['country'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-field">
                            <label class="form-label"><?= Html::encode(Lang::t('admin_city')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="<?= Html::encode($model['city'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_website')) ?></label>
                    <input type="url" name="website" class="form-control" placeholder="https://..." value="<?= Html::encode($model['website'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_2')) ?></span></h3>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_contact_name')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="contact_name" class="form-control" value="<?= Html::encode($model['contact_name'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_contact_position')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="contact_position" class="form-control" value="<?= Html::encode($model['contact_position'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_email')) ?> <span class="text-danger">*</span></label>
                    <input type="email" name="contact_email" class="form-control" value="<?= Html::encode($model['contact_email'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_phone')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= Html::encode($model['contact_phone'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_contact_method')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="contact_method" class="form-control" placeholder="<?= Html::encode(Lang::t('admin_contact_method_ph')) ?>" value="<?= Html::encode($model['contact_method'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_3')) ?> <span class="text-danger">*</span></span></h3>
                <ul class="admin-form-bullet-list">
                <?php foreach ($coopOptions as $val => $label): ?>
                    <li class="admin-form-bullet-item">
                        <span class="admin-form-bullet-dot"></span>
                        <input class="form-check-input" type="checkbox" name="cooperation_directions[]" value="<?= Html::encode($val) ?>" id="coop_<?= Html::encode($val) ?>" <?= in_array($val, $coopDecoded, true) ? 'checked' : '' ?>>
                        <label for="coop_<?= Html::encode($val) ?>"><?= Html::encode($label) ?></label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <div class="admin-form-other-wrap">
                    <label class="form-label small"><?= Html::encode(Lang::t('admin_other_specify')) ?></label>
                    <input type="text" name="cooperation_directions_other" class="form-control form-control-sm" placeholder="<?= Html::encode(Lang::t('admin_coop_other_ph')) ?>" value="<?= Html::encode(implode(', ', array_filter($coopDecoded, fn($v) => !isset($coopOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_4')) ?> <span class="text-danger">*</span></span></h3>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_description')) ?> <span class="text-danger">*</span></label>
                    <div class="lang-field lang-field-ru<?= $formLocale === 'ru' ? '' : ' d-none' ?>">
                        <textarea name="description_ru" class="form-control" rows="5" placeholder="<?= Html::encode(Lang::t('admin_description_ph')) ?>"><?= Html::encode($displayDescriptionRu) ?></textarea>
                    </div>
                    <div class="lang-field lang-field-en<?= $formLocale === 'en' ? '' : ' d-none' ?>">
                        <textarea name="description_en" class="form-control" rows="5" placeholder="<?= Html::encode(Lang::t('admin_description_ph')) ?>"><?= Html::encode($displayDescriptionEn) ?></textarea>
                    </div>
                </div>
                <div class="admin-form-field">
                    <label class="form-label"><?= Html::encode(Lang::t('admin_desc_images')) ?></label>
                    <input type="file" name="description_images[]" id="desc-images-input" class="d-none" accept="image/*" multiple>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" id="desc-images-select-btn" class="btn btn-outline-secondary btn-sm"><?= Html::encode(Lang::t('admin_choose_files')) ?></button>
                        <span id="desc-images-selected-text" class="small text-muted"><?= Html::encode(Lang::t('admin_no_files_chosen')) ?></span>
                    </div>
                    <small class="text-muted"><?= Html::encode(Lang::t('admin_desc_images_hint')) ?></small>
                    <?php
                    $descImages = [];
                    if ($model && !empty($model['description_images']) && is_string($model['description_images'])) {
                        $decoded = json_decode($model['description_images'], true);
                        if (is_array($decoded)) {
                            $descImages = $decoded;
                        }
                    }
                    ?>
                    <?php if (!empty($descImages)): ?>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <?php foreach ($descImages as $src): ?>
                                <?php if (!is_string($src) || $src === '') { continue; } ?>
                                <label class="d-inline-flex flex-column align-items-center gap-1 file-remove-item">
                                    <img src="<?= Html::encode($src) ?>" alt="" class="admin-form-description-preview" width="80" height="80" style="object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                    <span class="small">
                                        <input type="checkbox" class="d-none file-remove-checkbox" name="remove_description_images[]" value="<?= Html::encode($src) ?>">
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 file-remove-btn" title="<?= Html::encode(Lang::t('admin_remove_file')) ?>" aria-label="<?= Html::encode(Lang::t('admin_remove_file')) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_5')) ?> <span class="text-danger">*</span></span></h3>
                <ul class="admin-form-bullet-list">
                <?php foreach ($areaOptions as $val => $label): ?>
                    <li class="admin-form-bullet-item">
                        <span class="admin-form-bullet-dot"></span>
                        <input class="form-check-input" type="checkbox" name="activity_areas[]" value="<?= Html::encode($val) ?>" id="area_<?= Html::encode($val) ?>" <?= in_array($val, $areasDecoded, true) ? 'checked' : '' ?>>
                        <label for="area_<?= Html::encode($val) ?>"><?= Html::encode($label) ?></label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <div class="admin-form-other-wrap">
                    <label class="form-label small"><?= Html::encode(Lang::t('admin_other_specify')) ?></label>
                    <input type="text" name="activity_areas_other" class="form-control form-control-sm" placeholder="<?= Html::encode(Lang::t('admin_area_other_ph')) ?>" value="<?= Html::encode(implode(', ', array_filter($areasDecoded, fn($v) => !isset($areaOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_6')) ?> <span class="text-danger">*</span></span></h3>
                <?php
                $formatDecoded = $model ? (is_string($model['interaction_format'] ?? '') ? json_decode($model['interaction_format'], true) : []) : [];
                $formatDecoded = is_array($formatDecoded) ? $formatDecoded : [];
                ?>
                <ul class="admin-form-bullet-list">
                <?php foreach ($formatOptions as $val => $label): ?>
                    <li class="admin-form-bullet-item">
                        <span class="admin-form-bullet-dot"></span>
                        <input class="form-check-input" type="checkbox" name="interaction_format[]" value="<?= Html::encode($val) ?>" id="format_<?= Html::encode($val) ?>" <?= in_array($val, $formatDecoded, true) ? 'checked' : '' ?>>
                        <label for="format_<?= Html::encode($val) ?>"><?= Html::encode($label) ?></label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <div class="admin-form-other-wrap">
                    <label class="form-label small"><?= Html::encode(Lang::t('admin_other_specify')) ?></label>
                    <input type="text" name="interaction_format_other" class="form-control form-control-sm" placeholder="<?= Html::encode(Lang::t('admin_format_other_ph')) ?>" value="<?= Html::encode(implode(', ', array_filter($formatDecoded, fn($v) => !isset($formatOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_projects')) ?></span></h3>
                <p class="admin-form-textarea-hint text-muted mb-3"><?= Html::encode(Lang::t('admin_projects_hint')) ?></p>
                <?php
                $projectsRuPrepared = [];
                $projectsEnPrepared = [];
                $projectsRuSource = $model ? Partnership::decodeJson($model['subtasks'] ?? null) : [];
                $projectsEnSource = $model ? Partnership::decodeJson($model['subtasks_en'] ?? null) : [];
                if ($projectsRuSource === [] && $projectsEnSource !== []) {
                    $projectsRuSource = $projectsEnSource;
                } elseif ($projectsEnSource === [] && $projectsRuSource !== []) {
                    $projectsEnSource = $projectsRuSource;
                }
                if (is_array($projectsRuSource)) {
                    foreach ($projectsRuSource as $project) {
                        if (!is_array($project)) {
                            continue;
                        }
                        $name = trim((string) ($project['name'] ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $imgRu = [];
                        if (is_array($project['images'] ?? null)) {
                            foreach ($project['images'] as $p) {
                                $p = is_string($p) ? trim($p) : '';
                                if ($p !== '' && str_starts_with($p, '/uploads/projects/')) {
                                    $imgRu[] = $p;
                                }
                            }
                        }
                        $projectsRuPrepared[] = [
                            'name' => $name,
                            'description' => (string) ($project['description'] ?? ''),
                            'goals' => is_array($project['goals'] ?? null) ? $project['goals'] : [],
                            'subtasks' => is_array($project['subtasks'] ?? null) ? $project['subtasks'] : [],
                            'ready' => (string) ($project['ready'] ?? ''),
                            'images' => $imgRu,
                        ];
                    }
                }
                if (is_array($projectsEnSource)) {
                    foreach ($projectsEnSource as $project) {
                        if (!is_array($project)) {
                            continue;
                        }
                        $name = trim((string) ($project['name'] ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $imgEn = [];
                        if (is_array($project['images'] ?? null)) {
                            foreach ($project['images'] as $p) {
                                $p = is_string($p) ? trim($p) : '';
                                if ($p !== '' && str_starts_with($p, '/uploads/projects/')) {
                                    $imgEn[] = $p;
                                }
                            }
                        }
                        $projectsEnPrepared[] = [
                            'name' => $name,
                            'description' => (string) ($project['description'] ?? ''),
                            'goals' => is_array($project['goals'] ?? null) ? $project['goals'] : [],
                            'subtasks' => is_array($project['subtasks'] ?? null) ? $project['subtasks'] : [],
                            'ready' => (string) ($project['ready'] ?? ''),
                            'images' => $imgEn,
                        ];
                    }
                }
                ?>
                <div class="lang-field lang-field-ru<?= $formLocale === 'ru' ? '' : ' d-none' ?>">
                    <div id="projects-container-ru"></div>
                    <button type="button" id="add-project-btn-ru" class="btn btn-outline-primary btn-sm mt-1">
                        <i class="bi bi-plus-circle me-1"></i> <?= Html::encode(Lang::t('admin_add_project')) ?> (RU)
                    </button>
                </div>
                <div class="lang-field lang-field-en<?= $formLocale === 'en' ? '' : ' d-none' ?>">
                    <div id="projects-container-en"></div>
                    <button type="button" id="add-project-btn-en" class="btn btn-outline-primary btn-sm mt-1">
                        <i class="bi bi-plus-circle me-1"></i> <?= Html::encode(Lang::t('admin_add_project')) ?> (EN)
                    </button>
                </div>
                <?php
                $projectsRuJson = json_encode($projectsRuPrepared, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $projectsEnJson = json_encode($projectsEnPrepared, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                if (!is_string($projectsRuJson)) {
                    $projectsRuJson = '[]';
                }
                if (!is_string($projectsEnJson)) {
                    $projectsEnJson = '[]';
                }
                $projectsRuJsonB64 = base64_encode($projectsRuJson);
                $projectsEnJsonB64 = base64_encode($projectsEnJson);
                ?>
                <input type="hidden" name="projects_json_ru" id="projects-hidden-ru" value="<?= Html::encode($projectsRuJson) ?>" data-json-b64="<?= Html::encode($projectsRuJsonB64) ?>">
                <input type="hidden" name="projects_json_en" id="projects-hidden-en" value="<?= Html::encode($projectsEnJson) ?>" data-json-b64="<?= Html::encode($projectsEnJsonB64) ?>">
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_9')) ?></span></h3>
                <p class="admin-form-textarea-hint text-muted mb-3"><?= Html::encode(Lang::t('admin_events_hint')) ?></p>
                <?php
                $eventsDecoded = $model ? Partnership::decodeJson($model['events'] ?? null) : [];
                $eventsDecoded = is_array($eventsDecoded) ? $eventsDecoded : [];
                ?>
                <div id="events-container">
                    <?php if (!empty($eventsDecoded)): ?>
                        <?php foreach ($eventsDecoded as $i => $ev): ?>
                            <?php
                            $ev = is_array($ev) ? $ev : [];
                            $date = $ev['date'] ?? $ev['date_event'] ?? '';
                            $title = $ev['title'] ?? $ev['name'] ?? '';
                            $location = $ev['location'] ?? $ev['city'] ?? $ev['place'] ?? '';
                            ?>
                            <div class="event-row mb-3 p-3 border rounded-3 bg-light position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 event-remove-btn" aria-label="<?= Html::encode(Lang::t('admin_aria_remove')) ?>"></button>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1"><?= Html::encode(Lang::t('admin_event_date')) ?></label>
                                        <input type="text" class="form-control event-date" placeholder="<?= Html::encode(Lang::t('admin_event_date_ph')) ?>" value="<?= Html::encode($date) ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1"><?= Html::encode(Lang::t('admin_event_title')) ?></label>
                                        <input type="text" class="form-control event-title" placeholder="<?= Html::encode(Lang::t('admin_event_title_ph')) ?>" value="<?= Html::encode($title) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1"><?= Html::encode(Lang::t('admin_event_location')) ?></label>
                                        <input type="text" class="form-control event-location" placeholder="<?= Html::encode(Lang::t('admin_event_loc_ph')) ?>" value="<?= Html::encode($location) ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-event-btn" class="btn btn-outline-primary btn-sm mt-1">
                    <i class="bi bi-plus-circle me-1"></i> <?= Html::encode(Lang::t('admin_add_event')) ?>
                </button>
                <input type="hidden" name="events" id="events-hidden" value="<?= Html::encode(json_encode($eventsDecoded, JSON_UNESCAPED_UNICODE)) ?>">
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span><?= Html::encode(Lang::t('admin_section_10')) ?></span></h3>
                <p class="small text-muted mb-2"><?= Html::encode(Lang::t('admin_materials_hint')) ?></p>
                <input type="file" name="materials[]" id="materials-input" class="d-none" multiple>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="materials-select-btn" class="btn btn-outline-secondary btn-sm"><?= Html::encode(Lang::t('admin_choose_files')) ?></button>
                    <span id="materials-selected-text" class="small text-muted"><?= Html::encode(Lang::t('admin_no_files_chosen')) ?></span>
                </div>
                <?php
                $materialsList = [];
                if ($model && !empty($model['materials']) && is_string($model['materials'])) {
                    $decodedMaterials = json_decode($model['materials'], true);
                    if (is_array($decodedMaterials)) {
                        $materialsList = $decodedMaterials;
                    }
                }
                ?>
                <?php if (!empty($materialsList)): ?>
                    <div class="mt-3">
                        <div class="form-label small mb-1"><?= Html::encode(Lang::t('admin_files_uploaded')) ?></div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($materialsList as $path): ?>
                                <?php if (!is_string($path) || $path === '') { continue; } ?>
                                <?php $name = basename(str_replace('\\', '/', $path)); ?>
                                <li class="mb-1 file-remove-item">
                                    <a href="<?= Html::encode($path) ?>" target="_blank" rel="noopener" download><?= Html::encode($name) ?></a>
                                    <label class="ms-2 small">
                                        <input type="checkbox" class="d-none file-remove-checkbox" name="remove_materials[]" value="<?= Html::encode($path) ?>">
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 file-remove-btn" title="<?= Html::encode(Lang::t('admin_remove_file')) ?>" aria-label="<?= Html::encode(Lang::t('admin_remove_file')) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="data_consent" value="1" id="data_consent" <?= !empty($model['data_consent']) ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="data_consent"><?= Html::encode(Lang::t('admin_consent')) ?> <span class="text-danger">*</span></label>
                </div>
            </div>
        </div>

        <div class="admin-form-actions d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= Html::encode($isEdit ? Lang::t('admin_save') : Lang::t('admin_create')) ?></button>
            <a href="<?= $urlGenerator->generate($cancelRoute) ?>" class="btn btn-outline-secondary"><?= Html::encode(Lang::t('admin_cancel')) ?></a>
        </div>
    </form>
</div>
</div>
<script>
var adminFormI18n = <?= $adminFormJsI18n ?>;
document.getElementById('org_type_select').addEventListener('change', function() {
    document.getElementById('org_type_other').style.display = this.value === 'other' ? 'block' : 'none';
});
if (document.getElementById('org_type_select').value === 'other') {
    document.getElementById('org_type_other').style.display = 'block';
}

document.querySelectorAll('.file-remove-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var item = btn.closest('.file-remove-item');
        var checkbox = item ? item.querySelector('.file-remove-checkbox') : null;
        var pending = document.getElementById('pending-removals');
        if (checkbox) {
            checkbox.checked = true;
            if (pending) {
                pending.appendChild(checkbox);
            }
        }
        if (item) {
            item.remove();
        }
    });
});

(function() {
    function initMultiPicker(inputId, btnId, textId) {
        var input = document.getElementById(inputId);
        var btn = document.getElementById(btnId);
        var text = document.getElementById(textId);
        if (!input || !btn || !text) {
            return;
        }
        var picked = [];

        function keyOf(file) {
            return [file.name, file.size, file.lastModified].join('|');
        }

        function syncInputFiles() {
            if (typeof DataTransfer === 'undefined') {
                return;
            }
            var dt = new DataTransfer();
            picked.forEach(function(file) { dt.items.add(file); });
            input.files = dt.files;
        }

        function updateText() {
            var count = picked.length;
            if (count === 0) {
                text.textContent = adminFormI18n.no_files_chosen || '';
                return;
            }
            if (count === 1) {
                text.textContent = picked[0].name || '';
                return;
            }
            text.textContent = count + ' ' + (adminFormI18n.files_chosen || '');
        }

        btn.addEventListener('click', function() {
            input.click();
        });
        input.addEventListener('change', function() {
            var files = input.files ? Array.prototype.slice.call(input.files) : [];
            if (!files.length) {
                updateText();
                return;
            }
            var map = {};
            picked.forEach(function(f) { map[keyOf(f)] = true; });
            files.forEach(function(f) {
                var k = keyOf(f);
                if (!map[k]) {
                    picked.push(f);
                    map[k] = true;
                }
            });
            syncInputFiles();
            updateText();
        });

        updateText();
    }

    initMultiPicker('materials-input', 'materials-select-btn', 'materials-selected-text');
    initMultiPicker('desc-images-input', 'desc-images-select-btn', 'desc-images-selected-text');

    (function() {
        var logoInput = document.getElementById('logo-file-input');
        var logoBtn = document.getElementById('logo-file-select-btn');
        if (logoInput && logoBtn) {
            logoBtn.addEventListener('click', function() {
                logoInput.click();
            });
        }
    })();
})();

document.querySelectorAll('.form-lang-tab').forEach(function(tabBtn) {
    tabBtn.addEventListener('click', function() {
        var lang = tabBtn.getAttribute('data-lang');
        if (!lang || (lang !== 'ru' && lang !== 'en')) {
            return;
        }
        try {
            var u = new URL(window.location.href);
            u.searchParams.set('lang', lang);
            window.location.assign(u.pathname + u.search + u.hash);
        } catch (e) {}
    });
});

(function() {
    var i18n = adminFormI18n;

    function splitLines(text) {
        return (text || '').split(/\r?\n/).map(function(v) { return v.trim(); }).filter(Boolean);
    }

    function escapeAttr(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    function bindProjectImagesPicker(row) {
        var input = row.querySelector('.project-images-input');
        var btn = row.querySelector('.project-images-select-btn');
        var text = row.querySelector('.project-images-selected-text');
        if (!input || !btn || !text) {
            return;
        }
        var picked = [];

        function keyOf(file) {
            return [file.name, file.size, file.lastModified].join('|');
        }

        function syncInputFiles() {
            if (typeof DataTransfer === 'undefined') {
                return;
            }
            var dt = new DataTransfer();
            picked.forEach(function(file) { dt.items.add(file); });
            input.files = dt.files;
        }

        function updateText() {
            var count = picked.length;
            if (count === 0) {
                text.textContent = i18n.no_files_chosen || '';
                return;
            }
            if (count === 1) {
                text.textContent = picked[0].name || '';
                return;
            }
            text.textContent = count + ' ' + (i18n.files_chosen || '');
        }

        btn.addEventListener('click', function() {
            input.click();
        });
        input.addEventListener('change', function() {
            var files = input.files ? Array.prototype.slice.call(input.files) : [];
            if (!files.length) {
                updateText();
                return;
            }
            var map = {};
            picked.forEach(function(f) { map[keyOf(f)] = true; });
            files.forEach(function(f) {
                var k = keyOf(f);
                if (!map[k]) {
                    picked.push(f);
                    map[k] = true;
                }
            });
            syncInputFiles();
            updateText();
        });
        updateText();
    }

    function createProjectRow(project, locale) {
        var item = project || {};
        var loc = locale === 'en' ? 'en' : 'ru';
        var removeImgName = 'remove_project_images_' + loc + '[]';
        var wrap = document.createElement('div');
        wrap.className = 'mb-3 p-3 border rounded-3 bg-light project-row position-relative';
        wrap.innerHTML =
            '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 project-remove-btn" aria-label="' + escapeAttr(i18n.remove || 'Remove') + '"></button>' +
            '<div class="mb-2">' +
                '<label class="form-label small mb-1">' + escapeAttr(i18n.project_name) + '</label>' +
                '<input type="text" class="form-control project-name" placeholder="' + escapeAttr(i18n.project_name_ph) + '" value="' + escapeAttr(item.name || '') + '">' +
            '</div>' +
            '<div class="mb-2">' +
                '<label class="form-label small mb-1">' + escapeAttr(i18n.project_description) + '</label>' +
                '<div class="d-flex gap-1 mb-1">' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary project-bold-btn" title="Bold"><i class="bi bi-type-bold"></i></button>' +
                '</div>' +
                '<div class="project-description-editor form-control" contenteditable="true" style="min-height:96px;"></div>' +
                '<textarea class="d-none project-description"></textarea>' +
            '</div>' +
            '<div class="mb-2 project-images-block">' +
                '<label class="form-label small mb-1">' + escapeAttr(i18n.project_images) + '</label>' +
                '<input type="file" class="project-images-input d-none" accept="image/*" multiple>' +
                '<div class="d-flex align-items-center gap-2 flex-wrap mb-1">' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary project-images-select-btn">' + escapeAttr(i18n.choose_files) + '</button>' +
                    '<span class="small text-muted project-images-selected-text"></span>' +
                '</div>' +
                '<small class="text-muted d-block mb-2">' + escapeAttr(i18n.project_images_hint) + '</small>' +
                '<div class="d-flex flex-wrap gap-2 project-images-existing"></div>' +
            '</div>' +
            '<div class="row g-2">' +
                '<div class="col-md-6">' +
                    '<label class="form-label small mb-1">' + escapeAttr(i18n.project_goals) + '</label>' +
                    '<textarea class="form-control project-goals" rows="3" placeholder="' + escapeAttr(i18n.project_goals_ph) + '"></textarea>' +
                '</div>' +
                '<div class="col-md-6">' +
                    '<label class="form-label small mb-1">' + escapeAttr(i18n.project_subtasks) + '</label>' +
                    '<textarea class="form-control project-subtasks" rows="3" placeholder="' + escapeAttr(i18n.project_subtasks_ph) + '"></textarea>' +
                '</div>' +
            '</div>' +
            '<div class="mt-2">' +
                '<label class="form-label small mb-1">' + escapeAttr(i18n.project_ready) + '</label>' +
                '<input type="text" class="form-control project-ready" placeholder="' + escapeAttr(i18n.project_ready_ph) + '" value="' + escapeAttr(item.ready || '') + '">' +
            '</div>';

        var goalsTa = wrap.querySelector('.project-goals');
        var subTa = wrap.querySelector('.project-subtasks');
        if (goalsTa) {
            goalsTa.value = (item.goals || []).join('\n');
        }
        if (subTa) {
            subTa.value = (item.subtasks || []).join('\n');
        }

        var exWrap = wrap.querySelector('.project-images-existing');
        if (exWrap && Array.isArray(item.images)) {
            item.images.forEach(function(src) {
                src = String(src || '').trim();
                if (!src || src.indexOf('/uploads/projects/') !== 0) {
                    return;
                }
                var label = document.createElement('label');
                label.className = 'd-inline-flex flex-column align-items-center gap-1 file-remove-item';
                var imgEl = document.createElement('img');
                imgEl.src = src;
                imgEl.alt = '';
                imgEl.className = 'admin-form-description-preview';
                imgEl.width = 80;
                imgEl.height = 80;
                imgEl.style.objectFit = 'cover';
                imgEl.style.borderRadius = '6px';
                imgEl.style.border = '1px solid #dee2e6';
                var span = document.createElement('span');
                span.className = 'small';
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.className = 'd-none file-remove-checkbox';
                cb.name = removeImgName;
                cb.value = src;
                var rb = document.createElement('button');
                rb.type = 'button';
                rb.className = 'btn btn-sm btn-outline-danger py-0 px-2 file-remove-btn';
                rb.title = i18n.remove_file || '';
                rb.setAttribute('aria-label', i18n.remove_file || '');
                rb.innerHTML = '<i class="bi bi-trash"></i>';
                span.appendChild(cb);
                span.appendChild(rb);
                label.appendChild(imgEl);
                label.appendChild(span);
                exWrap.appendChild(label);
            });
        }

        wrap.querySelector('.project-remove-btn').addEventListener('click', function() {
            wrap.remove();
        });

        bindProjectImagesPicker(wrap);

        var desc = wrap.querySelector('.project-description');
        var editor = wrap.querySelector('.project-description-editor');
        if (editor) {
            editor.innerHTML = item.description || '';
        }
        var boldBtn = wrap.querySelector('.project-bold-btn');
        if (boldBtn && editor) {
            boldBtn.addEventListener('mousedown', function(e) { e.preventDefault(); });
            boldBtn.addEventListener('click', function() {
                editor.focus();
                try { document.execCommand('bold'); } catch (err) {}
            });
        }
        if (editor && desc) {
            var syncDesc = function() { desc.value = editor.innerHTML; };
            editor.addEventListener('input', syncDesc);
            syncDesc();
        }
        return wrap;
    }

    function collectProjects(container, hidden) {
        if (!container || !hidden) {
            return;
        }
        var rows = container.querySelectorAll('.project-row');
        var out = [];
        rows.forEach(function(row) {
            var name = (row.querySelector('.project-name') || {}).value || '';
            name = name.trim();
            if (!name) {
                return;
            }
            var images = [];
            row.querySelectorAll('.project-images-existing .file-remove-item').forEach(function(lab) {
                var cb = lab.querySelector('.file-remove-checkbox');
                if (cb && cb.value && String(cb.value).indexOf('/uploads/projects/') === 0) {
                    images.push(String(cb.value).trim());
                }
            });
            out.push({
                name: name,
                description: ((row.querySelector('.project-description') || {}).value || '').trim(),
                goals: splitLines((row.querySelector('.project-goals') || {}).value || ''),
                subtasks: splitLines((row.querySelector('.project-subtasks') || {}).value || ''),
                ready: ((row.querySelector('.project-ready') || {}).value || '').trim(),
                images: images
            });
        });
        hidden.value = JSON.stringify(out);
    }

    function initProjectsEditor(container, hidden, addBtn, locale) {
        if (!container || !hidden) {
            return;
        }
        var loc = locale === 'en' ? 'en' : 'ru';
        function parseProjectsRaw(raw) {
            if (!raw || typeof raw !== 'string') {
                return [];
            }
            try {
                var parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }
        try {
            var initial = parseProjectsRaw(hidden.value || '[]');
            if ((!Array.isArray(initial) || initial.length === 0) && hidden.dataset && hidden.dataset.jsonB64) {
                try {
                    var decoded = atob(hidden.dataset.jsonB64);
                    var fromB64 = parseProjectsRaw(decoded);
                    if (Array.isArray(fromB64) && fromB64.length > 0) {
                        initial = fromB64;
                    }
                } catch (e) {}
            }
            if (Array.isArray(initial) && initial.length) {
                initial.forEach(function(item) {
                    container.appendChild(createProjectRow(item, loc));
                });
            } else {
                container.appendChild(createProjectRow({}, loc));
            }
        } catch (e) {
            container.appendChild(createProjectRow({}, loc));
        }
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                container.appendChild(createProjectRow({}, loc));
            });
        }
        collectProjects(container, hidden);
    }

    function reindexProjectImageInputs(container, locale) {
        if (!container) {
            return;
        }
        var loc = locale === 'en' ? 'en' : 'ru';
        var rows = container.querySelectorAll('.project-row');
        var idx = 0;
        rows.forEach(function(row) {
            var name = (row.querySelector('.project-name') || {}).value || '';
            var input = row.querySelector('.project-images-input');
            if (!input) {
                return;
            }
            if (!name.trim()) {
                input.removeAttribute('name');
                return;
            }
            input.setAttribute('name', 'project_images_' + loc + '[' + idx + '][]');
            idx++;
        });
    }

    var projectsContainerRu = document.getElementById('projects-container-ru');
    var projectsHiddenRu = document.getElementById('projects-hidden-ru');
    var addProjectBtnRu = document.getElementById('add-project-btn-ru');
    initProjectsEditor(projectsContainerRu, projectsHiddenRu, addProjectBtnRu, 'ru');

    var projectsContainerEn = document.getElementById('projects-container-en');
    var projectsHiddenEn = document.getElementById('projects-hidden-en');
    var addProjectBtnEn = document.getElementById('add-project-btn-en');
    initProjectsEditor(projectsContainerEn, projectsHiddenEn, addProjectBtnEn, 'en');

    var formForProjects = document.querySelector('form');
    if (formForProjects) {
        formForProjects.addEventListener('submit', function() {
            reindexProjectImageInputs(projectsContainerRu, 'ru');
            reindexProjectImageInputs(projectsContainerEn, 'en');
            collectProjects(projectsContainerRu, projectsHiddenRu);
            collectProjects(projectsContainerEn, projectsHiddenEn);
        });
    }
})();

(function() {
    var container = document.getElementById('events-container');
    var addBtn = document.getElementById('add-event-btn');
    var hiddenInput = document.getElementById('events-hidden');
    var i18n = adminFormI18n;

    function createEventRow(date, title, location) {
        var div = document.createElement('div');
        div.className = 'event-row mb-3 p-3 border rounded-3 bg-light position-relative';
        div.innerHTML =
            '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 event-remove-btn" aria-label="' + (i18n.remove || 'Remove') + '"></button>' +
            '<div class="row g-2">' +
                '<div class="col-md-3">' +
                    '<label class="form-label small mb-1">' + i18n.event_date + '</label>' +
                    '<input type="text" class="form-control event-date" placeholder="' + i18n.event_date_ph + '" value="' + (date || '') + '">' +
                '</div>' +
                '<div class="col-md-5">' +
                    '<label class="form-label small mb-1">' + i18n.event_title + '</label>' +
                    '<input type="text" class="form-control event-title" placeholder="' + i18n.event_title_ph + '" value="' + (title || '') + '">' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<label class="form-label small mb-1">' + i18n.event_location + '</label>' +
                    '<input type="text" class="form-control event-location" placeholder="' + i18n.event_loc_ph + '" value="' + (location || '') + '">' +
                '</div>' +
            '</div>';
        div.querySelector('.event-remove-btn').addEventListener('click', function() {
            div.remove();
            collectEvents();
        });
        return div;
    }

    function collectEvents() {
        var rows = container.querySelectorAll('.event-row');
        var events = [];
        rows.forEach(function(row) {
            var date = row.querySelector('.event-date').value.trim();
            var title = row.querySelector('.event-title').value.trim();
            var location = row.querySelector('.event-location').value.trim();
            if (date || title) {
                events.push({date: date, title: title, location: location});
            }
        });
        hiddenInput.value = JSON.stringify(events);
    }

    container.querySelectorAll('.event-remove-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.event-row').remove();
            collectEvents();
        });
    });

    addBtn.addEventListener('click', function() {
        container.appendChild(createEventRow('', '', ''));
    });

    var form = container.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            collectEvents();
        });
    }
})();
</script>
