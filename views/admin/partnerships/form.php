<?php

declare(strict_types=1);

use App\Model\Partnership;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;

/** @var array|null $model */
/** @var array $errors */
/** @var UrlGeneratorInterface $urlGenerator */
$isEdit = $model !== null && isset($model['id']);
$this->setParameter('pageTitle', $isEdit ? 'Редактировать организацию' : 'Новая организация');
$action = $isEdit ? $urlGenerator->generate('admin/partnerships/edit-post', ['id' => $model['id']]) : $urlGenerator->generate('admin/partnerships/create-post');
$coopDecoded = $model ? (is_string($model['cooperation_directions'] ?? '') ? json_decode($model['cooperation_directions'], true) : []) : [];
$areasDecoded = $model ? (is_string($model['activity_areas'] ?? '') ? json_decode($model['activity_areas'], true) : []) : [];
$coopDecoded = is_array($coopDecoded) ? $coopDecoded : [];
$areasDecoded = is_array($areasDecoded) ? $areasDecoded : [];

$orgTypes = [
    'company' => 'Компания',
    'university' => 'Университет',
    'research' => 'Исследовательский центр',
    'government' => 'Государственная организация',
    'ngo' => 'НКО',
    'other' => 'Другое',
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
?>
<div class="admin-form-page">
<div class="container py-4">
    <h1 class="admin-form-title"><?= $isEdit ? 'Редактировать организацию' : 'Новая организация' ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= Html::encode(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= Html::encode($action) ?>" enctype="multipart/form-data">
        <?php $csrf = $this->getParameter('csrf'); if ($csrf): ?><input type="hidden" name="<?= Html::encode($csrf->getParameterName()) ?>" value="<?= Html::encode($csrf->getToken()) ?>"><?php endif; ?>

        <div class="admin-form-card">
            <div class="admin-form-teal-banner">
                <div class="admin-form-teal-placeholder">
                    <?php if (!empty($model['file_path'])): ?>
                        <?php $imgUrl = '/serve/partnership?f=' . rawurlencode(basename(str_replace('\\', '/', $model['file_path']))); ?>
                        <img src="<?= Html::encode($imgUrl) ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label d-block small mb-1 text-white">Название организации / компания <span class="text-danger">*</span></label>
                    <input type="text" name="org_name" class="form-control bg-white border-0" value="<?= Html::encode($model['org_name'] ?? '') ?>" placeholder="Имя проекта" required style="max-width: 100%;">
                </div>
                <div class="align-self-end">
                    <label class="form-label small mb-1 d-block text-white">Логотип <span class="opacity-75">*</span></label>
                    <input type="file" name="file" class="form-control form-control-sm bg-white border-0" accept="image/*" style="max-width: 180px;">
                </div>
            </div>
            <div class="card-body">
                <div class="admin-form-field">
                    <label class="form-label">Тип организации <span class="text-danger">*</span></label>
                    <?php
                    $orgTypeVal = $model['org_type'] ?? '';
                    $orgTypeInList = $orgTypeVal !== '' && isset($orgTypes[$orgTypeVal]);
                    $orgTypeOtherVal = $orgTypeInList ? '' : $orgTypeVal;
                    $orgTypeSelect = $orgTypeInList ? $orgTypeVal : ($orgTypeVal !== '' ? 'other' : '');
                    ?>
                    <select name="org_type" class="form-select" id="org_type_select" required>
                        <option value="" <?= $orgTypeSelect === '' ? 'selected' : '' ?>>— Выберите —</option>
                        <?php foreach ($orgTypes as $val => $label): ?>
                            <option value="<?= Html::encode($val) ?>" <?= $orgTypeSelect === $val ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="org_type_other" class="form-control mt-1" id="org_type_other" placeholder="Укажите свой вариант" value="<?= Html::encode($orgTypeOtherVal) ?>" style="display:none">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="admin-form-field">
                            <label class="form-label">Страна <span class="text-danger">*</span></label>
                            <input type="text" name="country" class="form-control" value="<?= Html::encode($model['country'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-field">
                            <label class="form-label">Город <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="<?= Html::encode($model['city'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Сайт организации</label>
                    <input type="url" name="website" class="form-control" placeholder="https://..." value="<?= Html::encode($model['website'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>2. Контактное лицо</span></h3>
                <div class="admin-form-field">
                    <label class="form-label">Имя и фамилия <span class="text-danger">*</span></label>
                    <input type="text" name="contact_name" class="form-control" value="<?= Html::encode($model['contact_name'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Должность <span class="text-danger">*</span></label>
                    <input type="text" name="contact_position" class="form-control" value="<?= Html::encode($model['contact_position'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="contact_email" class="form-control" value="<?= Html::encode($model['contact_email'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Телефон <span class="text-danger">*</span></label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= Html::encode($model['contact_phone'] ?? '') ?>" required>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Предпочитаемый способ связи <span class="text-danger">*</span></label>
                    <input type="text" name="contact_method" class="form-control" placeholder="например: email, телефон" value="<?= Html::encode($model['contact_method'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>3. Направления сотрудничества <span class="text-danger">*</span></span></h3>
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
                    <label class="form-label small">Другое (напишите своё)</label>
                    <input type="text" name="cooperation_directions_other" class="form-control form-control-sm" placeholder="Своё направление" value="<?= Html::encode(implode(', ', array_filter($coopDecoded, fn($v) => !isset($coopOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>4. Краткое описание предложения <span class="text-danger">*</span></span></h3>
                <div class="admin-form-field">
                    <textarea name="description" class="form-control" rows="5" placeholder="Свободный текст" required><?= Html::encode($model['description'] ?? '') ?></textarea>
                </div>
                <div class="admin-form-field">
                    <label class="form-label">Изображения к описанию</label>
                    <input type="file" name="description_images[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">Можно прикрепить несколько фото (JPG, PNG, GIF, WebP). Необязательно.</small>
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
                                <img src="<?= Html::encode($src) ?>" alt="" class="admin-form-description-preview" width="80" height="80" style="object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>5. Область деятельности <span class="text-danger">*</span></span></h3>
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
                    <label class="form-label small">Другое (напишите своё)</label>
                    <input type="text" name="activity_areas_other" class="form-control form-control-sm" placeholder="Своя область" value="<?= Html::encode(implode(', ', array_filter($areasDecoded, fn($v) => !isset($areaOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>6. Возможный формат взаимодействия <span class="text-danger">*</span></span></h3>
                <?php
                $formatDecoded = $model ? (is_string($model['interaction_format'] ?? '') ? json_decode($model['interaction_format'], true) : []) : [];
                $formatDecoded = is_array($formatDecoded) ? $formatDecoded : [];
                $formatOptions = [
                    'joint_research' => 'Совместные исследования',
                    'contract_research' => 'Заказные исследования',
                    'staff_training' => 'Обучение сотрудников',
                    'joint_lab' => 'Совместная лаборатория',
                    'industrial_projects' => 'Индустриальные проекты',
                    'student_internships' => 'Практика студентов',
                ];
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
                    <label class="form-label small">Другое (напишите своё)</label>
                    <input type="text" name="interaction_format_other" class="form-control form-control-sm" placeholder="Свой формат" value="<?= Html::encode(implode(', ', array_filter($formatDecoded, fn($v) => !isset($formatOptions[$v])))) ?>">
                </div>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>7. Подзадачи проекта</span></h3>
                <p class="admin-form-textarea-hint text-muted mb-2">Каждая строка — новый пункт. Отображаются на странице проекта.</p>
                <?php
                $subtasksDecoded = $model ? Partnership::decodeJson($model['subtasks'] ?? null) : [];
                $subtasksText = is_array($subtasksDecoded) ? implode("\n", array_map(fn($v) => is_string($v) ? $v : (string) $v, $subtasksDecoded)) : '';
                ?>
                <textarea name="subtasks" class="form-control" rows="4" placeholder="Подзадача 1&#10;Подзадача 2"><?= Html::encode($subtasksText) ?></textarea>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>8. Цели проекта</span></h3>
                <p class="admin-form-textarea-hint text-muted mb-2">Каждая строка — новый пункт.</p>
                <?php
                $goalsDecoded = $model ? Partnership::decodeJson($model['goals'] ?? null) : [];
                $goalsText = is_array($goalsDecoded) ? implode("\n", array_map(fn($v) => is_string($v) ? $v : (string) $v, $goalsDecoded)) : '';
                ?>
                <textarea name="goals" class="form-control" rows="4" placeholder="Цель 1&#10;Цель 2"><?= Html::encode($goalsText) ?></textarea>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>9. Встречи и мероприятия</span></h3>
                <p class="admin-form-textarea-hint text-muted mb-2">Формат JSON. Данные отображаются в Timeline на странице проекта.</p>
                <?php
                $eventsDecoded = $model ? Partnership::decodeJson($model['events'] ?? null) : [];
                $eventsJson = is_array($eventsDecoded) ? Partnership::encodeJson($eventsDecoded) : '[]';
                ?>
                <textarea name="events" class="form-control admin-form-json-editor" rows="8" placeholder='[{"date":"12.05.2024","title":"Конференция по образованию","location":"Париж, Франция"}]'><?= Html::encode($eventsJson) ?></textarea>
            </div>
        </div>

        <div class="admin-form-card">
            <div class="card-body">
                <h3 class="admin-form-section-heading"><span>10. Дополнительные материалы</span></h3>
                <p class="small text-muted mb-2">Необязательно. Можно прикрепить документы (Word, PDF, презентации и др.), которые потом можно будет скачать на странице проекта.</p>
                <input type="file" name="materials[]" class="form-control" multiple>
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
                        <div class="form-label small mb-1">Уже загруженные файлы:</div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($materialsList as $path): ?>
                                <?php if (!is_string($path) || $path === '') { continue; } ?>
                                <?php $name = basename(str_replace('\\', '/', $path)); ?>
                                <li class="mb-1">
                                    <a href="<?= Html::encode($path) ?>" target="_blank" rel="noopener" download><?= Html::encode($name) ?></a>
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
                    <label class="form-check-label" for="data_consent">Согласие на обработку персональных данных <span class="text-danger">*</span></label>
                </div>
            </div>
        </div>

        <div class="admin-form-actions d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить' : 'Создать' ?></button>
            <a href="<?= $urlGenerator->generate('admin/partnerships') ?>" class="btn btn-outline-secondary">Отмена</a>
        </div>
    </form>
</div>
</div>
<script>
document.getElementById('org_type_select').addEventListener('change', function() {
    document.getElementById('org_type_other').style.display = this.value === 'other' ? 'block' : 'none';
});
if (document.getElementById('org_type_select').value === 'other') {
    document.getElementById('org_type_other').style.display = 'block';
}
// Валидация JSON для поля «Встречи»
(function() {
    var eventsField = document.querySelector('textarea[name="events"]');
    if (eventsField) {
        eventsField.addEventListener('blur', function() {
            var val = this.value.trim();
            if (val === '' || val === '[]') return;
            try {
                JSON.parse(val);
                this.setCustomValidity('');
            } catch (e) {
                this.setCustomValidity('Неверный формат JSON. Пример: [{"date":"12.05.2024","title":"Событие","location":"Город"}]');
            }
        });
    }
})();
</script>
