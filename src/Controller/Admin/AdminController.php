<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Partnership;
use App\Service\AuthService;
use App\Service\Lang;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface;

final class AdminController
{
    public function __construct(
        private WebViewRenderer $view,
        private ConnectionInterface $db,
        private AuthService $auth,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function login(): ResponseInterface
    {
        if (!$this->auth->isGuest()) {
            return $this->redirect('admin/dashboard');
        }
        return $this->view->render('admin/login', ['model' => ['username' => '', 'password' => '', 'rememberMe' => false]]);
    }

    public function loginPost(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $username = (string) ($body['username'] ?? '');
        $password = (string) ($body['password'] ?? '');
        if ($username === '' || $password === '') {
            return $this->view->render('admin/login', [
                'model' => ['username' => $username, 'password' => '', 'rememberMe' => false],
                'errors' => ['Неверный логин или пароль.'],
            ]);
        }
        if (!$this->auth->login($username, $password)) {
            return $this->view->render('admin/login', [
                'model' => ['username' => $username, 'password' => '', 'rememberMe' => !empty($body['rememberMe'])],
                'errors' => ['Неверный логин или пароль.'],
            ]);
        }
        return $this->redirect('admin/partnerships');
    }

    public function logout(): ResponseInterface
    {
        $this->auth->logout();
        return $this->redirect('admin/login');
    }

    public function dashboard(): ResponseInterface
    {
        $stats = [
            'totalPartnerships' => Partnership::countAll($this->db),
            'totalUsers' => \App\Model\User::countAll($this->db),
        ];
        return $this->view->render('admin/dashboard', ['stats' => $stats]);
    }

    public function partnershipsIndex(): ResponseInterface
    {
        $list = Partnership::findAll($this->db);
        return $this->view->render('admin/partnerships/index', ['list' => $list]);
    }

    public function partnershipCreate(): ResponseInterface
    {
        $isPublic = $this->auth->isGuest();

        return $this->view->render('admin/partnerships/form', [
            'model' => null,
            'errors' => [],
            'isPublic' => $isPublic,
            'createActionRoute' => $isPublic ? 'public/partnerships/create-post' : 'admin/partnerships/create-post',
            'cancelRoute' => $isPublic ? 'home' : 'admin/partnerships',
        ]);
    }

    public function partnershipCreatePost(ServerRequestInterface $request): ResponseInterface
    {
        $parsed = $this->partnershipDataFromRequest($request);
        $data = $this->mergePartnerLocaleFieldsForSave(null, $parsed);
        $filePath = $this->partnershipHandleUpload($request);
        if ($filePath !== null) {
            $data['file_path'] = $filePath;
        }
        $errors = $this->partnershipValidate($data, true, $data['file_path'] ?? null);
        if ($errors !== []) {
            $forForm = $this->partnershipFormModelForView(null, $parsed, $data);
            $isPublic = $this->auth->isGuest();

            return $this->view->render('admin/partnerships/form', [
                'model' => $forForm,
                'errors' => $errors,
                'isPublic' => $isPublic,
                'createActionRoute' => $isPublic ? 'public/partnerships/create-post' : 'admin/partnerships/create-post',
                'cancelRoute' => $isPublic ? 'home' : 'admin/partnerships',
            ]);
        }
        $data = $this->partnershipMergeProjectAssetsInto($request, $data);
        $materialsJson = $this->partnershipHandleMaterialsUpload($request, null);
        if ($materialsJson !== null) {
            $data['materials'] = $materialsJson;
        }
        $descImagesJson = $this->partnershipHandleDescriptionImagesUpload($request, null);
        if ($descImagesJson !== null) {
            $data['description_images'] = $descImagesJson;
        }
        $data['data_consent'] = !empty($data['data_consent']) ? 1 : 0;
        // New submissions are hidden from public pages until approved by admin.
        $data['published'] = 0;
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $this->db->createCommand()->insert('{{%partnership}}', $data)->execute();
        // Публичная форма отправляет на /partnerships/create (без /admin в пути),
        // а админская — на /admin/partnerships/create.
        $path = $request->getUri()->getPath();
        $isAdminRoute = str_starts_with($path, '/admin');

        return $isAdminRoute
            ? $this->redirect('admin/partnerships')
            : $this->redirect('home');
    }

    public function partnershipEdit(#[RouteArgument('id')] string $id): ResponseInterface
    {
        $model = Partnership::findById($this->db, (int) $id);
        if ($model === null) {
            throw new \RuntimeException('Not found', 404);
        }
        return $this->view->render('admin/partnerships/form', ['model' => $model, 'errors' => []]);
    }

    public function partnershipEditPost(ServerRequestInterface $request, #[RouteArgument('id')] string $id): ResponseInterface
    {
        $model = Partnership::findById($this->db, (int) $id);
        if ($model === null) {
            throw new \RuntimeException('Not found', 404);
        }
        $parsed = $this->partnershipDataFromRequest($request);
        $data = $this->mergePartnerLocaleFieldsForSave($model, $parsed);
        $filePath = $this->partnershipHandleUpload($request);
        if ($filePath !== null) {
            $data['file_path'] = $filePath;
        }
        $data['file_path'] = $data['file_path'] ?? $model['file_path'] ?? null;
        $errors = $this->partnershipValidate($data, false, $data['file_path']);
        if ($errors !== []) {
            $forForm = $this->partnershipFormModelForView($model, $parsed, $data);

            return $this->view->render('admin/partnerships/form', ['model' => $forForm, 'errors' => $errors]);
        }
        $data = $this->partnershipMergeProjectAssetsInto($request, $data);
        $existingMaterials = Partnership::decodeJson($model['materials'] ?? null);
        $materialsJson = $this->partnershipHandleMaterialsUpload($request, $existingMaterials);
        if ($materialsJson !== null) {
            $data['materials'] = $materialsJson;
        }
        $existingDescImages = Partnership::decodeJson($model['description_images'] ?? null);
        $descImagesJson = $this->partnershipHandleDescriptionImagesUpload($request, $existingDescImages);
        if ($descImagesJson !== null) {
            $data['description_images'] = $descImagesJson;
        }
        $data['data_consent'] = !empty($data['data_consent']) ? 1 : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        unset($data['created_at']);
        $this->db->createCommand()->update('{{%partnership}}', $data, ['id' => $id])->execute();
        return $this->redirect('admin/partnerships');
    }

    public function partnershipDelete(ServerRequestInterface $request, #[RouteArgument('id')] string $id): ResponseInterface
    {
        $this->db->createCommand()->delete('{{%partnership}}', ['id' => $id])->execute();
        return $this->redirect('admin/partnerships');
    }

    public function partnershipApprove(ServerRequestInterface $request, #[RouteArgument('id')] string $id): ResponseInterface
    {
        $this->db->createCommand()->update(
            '{{%partnership}}',
            [
                'published' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            ['id' => $id],
        )->execute();

        return $this->redirect('admin/partnerships');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function partnershipMergeProjectAssetsInto(ServerRequestInterface $request, array $data): array
    {
        $projectsRu = Partnership::decodeJson($data['subtasks'] ?? null);
        $projectsEn = Partnership::decodeJson($data['subtasks_en'] ?? null);
        $projectsKz = Partnership::decodeJson($data['subtasks_kz'] ?? null);
        $projectsRu = is_array($projectsRu) ? $projectsRu : [];
        $projectsEn = is_array($projectsEn) ? $projectsEn : [];
        $projectsKz = is_array($projectsKz) ? $projectsKz : [];

        $projectsRu = $this->partnershipMergeProjectImagesForLocale($request, $projectsRu, 'ru');
        $projectsEn = $this->partnershipMergeProjectImagesForLocale($request, $projectsEn, 'en');
        $projectsKz = $this->partnershipMergeProjectImagesForLocale($request, $projectsKz, 'kz');

        $data['subtasks'] = Partnership::encodeJson($projectsRu);
        $data['subtasks_en'] = Partnership::encodeJson($projectsEn);
        $data['subtasks_kz'] = Partnership::encodeJson($projectsKz);

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $projects
     * @return list<array<string, mixed>>
     */
    private function partnershipMergeProjectImagesForLocale(ServerRequestInterface $request, array $projects, string $locale): array
    {
        $body = $request->getParsedBody() ?? [];
        $removeKey = 'remove_project_images_' . $locale;
        $toRemove = is_array($body[$removeKey] ?? null) ? $body[$removeKey] : [];
        $toRemove = array_values(array_filter(
            array_map(static fn($v) => is_string($v) ? trim($v) : '', $toRemove),
            static fn($v) => $v !== '',
        ));

        $filesTree = $request->getUploadedFiles()['project_images_' . $locale] ?? null;

        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/projects';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($projects as $i => &$project) {
            if (!is_array($project)) {
                continue;
            }
            $images = [];
            if (isset($project['images']) && is_array($project['images'])) {
                foreach ($project['images'] as $path) {
                    $path = is_string($path) ? trim($path) : '';
                    if ($path === '' || !str_starts_with($path, '/uploads/projects/')) {
                        continue;
                    }
                    if (in_array($path, $toRemove, true)) {
                        $this->deletePublicUploadIfSafe($path);
                        continue;
                    }
                    $images[] = $path;
                }
            }
            $bucket = null;
            if (is_array($filesTree) && array_key_exists($i, $filesTree)) {
                $bucket = $filesTree[$i];
            }
            foreach ($this->flattenUploadedFileNodes($bucket) as $file) {
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    continue;
                }
                $name = $file->getClientFilename();
                if ($name === '' || $name === null) {
                    continue;
                }
                $mediaType = $file->getClientMediaType();
                if (!in_array($mediaType, $allowed, true)) {
                    continue;
                }
                $safe = $this->buildUploadFileName($name, $dir);
                $path = $dir . '/' . $safe;
                $file->moveTo($path);
                $publicPath = '/uploads/projects/' . $safe;
                if (!in_array($publicPath, $images, true)) {
                    $images[] = $publicPath;
                }
            }
            $project['images'] = $images;
        }
        unset($project);

        return $projects;
    }

    /**
     * @return list<UploadedFileInterface>
     */
    private function flattenUploadedFileNodes(mixed $node): array
    {
        if ($node instanceof UploadedFileInterface) {
            return $node->getError() === UPLOAD_ERR_NO_FILE ? [] : [$node];
        }
        if (!is_array($node)) {
            return [];
        }
        $out = [];
        foreach ($node as $v) {
            foreach ($this->flattenUploadedFileNodes($v) as $f) {
                $out[] = $f;
            }
        }

        return $out;
    }

    private function partnershipDataFromRequest(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody() ?? [];
        $formLocale = in_array(Lang::get(), ['ru', 'en', 'kz'], true) ? Lang::get() : 'ru';

        $coopKeys = is_array($body['cooperation_directions'] ?? null) ? $body['cooperation_directions'] : [];
        $coopDesc = is_array($body['cooperation_directions_desc'] ?? null) ? $body['cooperation_directions_desc'] : [];
        $coop = [];
        foreach ($coopKeys as $k) {
            $coop[$k] = trim((string) ($coopDesc[$k] ?? ''));
        }
        $coopOther = trim((string) ($body['cooperation_directions_other'] ?? ''));
        if ($coopOther !== '') {
            $coop[$coopOther] = trim((string) ($body['cooperation_directions_other_desc'] ?? ''));
        }

        $areasKeys = is_array($body['activity_areas'] ?? null) ? $body['activity_areas'] : [];
        $areasDesc = is_array($body['activity_areas_desc'] ?? null) ? $body['activity_areas_desc'] : [];
        $areas = [];
        foreach ($areasKeys as $k) {
            $areas[$k] = trim((string) ($areasDesc[$k] ?? ''));
        }
        $areasOther = trim((string) ($body['activity_areas_other'] ?? ''));
        if ($areasOther !== '') {
            $areas[$areasOther] = trim((string) ($body['activity_areas_other_desc'] ?? ''));
        }

        $formatKeys = is_array($body['interaction_format'] ?? null) ? $body['interaction_format'] : [];
        $formatDesc = is_array($body['interaction_format_desc'] ?? null) ? $body['interaction_format_desc'] : [];
        $format = [];
        foreach ($formatKeys as $k) {
            $format[$k] = trim((string) ($formatDesc[$k] ?? ''));
        }
        $formatOther = trim((string) ($body['interaction_format_other'] ?? ''));
        if ($formatOther !== '') {
            $format[$formatOther] = trim((string) ($body['interaction_format_other_desc'] ?? ''));
        }

        $orgType = trim((string) ($body['org_type'] ?? ''));
        if ($orgType === 'other') {
            $orgType = trim((string) ($body['org_type_other'] ?? ''));
        }
        $projectsRu = $this->parseProjectsJson((string) ($body['projects_json_ru'] ?? ''));
        $projectsEn = $this->parseProjectsJson((string) ($body['projects_json_en'] ?? ''));
        $projectsKz = $this->parseProjectsJson((string) ($body['projects_json_kz'] ?? ''));
        if ($projectsRu === [] && $projectsEn !== []) {
            $projectsRu = $projectsEn;
        }
        if ($projectsRu === [] && $projectsKz !== []) {
            $projectsRu = $projectsKz;
        }
        if ($projectsEn === [] && $projectsRu !== []) {
            $projectsEn = $projectsRu;
        }
        if ($projectsKz === [] && $projectsRu !== []) {
            $projectsKz = $projectsRu;
        }

        $eventsRaw = trim((string) ($body['events'] ?? ''));
        $events = [];
        if ($eventsRaw !== '') {
            $decoded = json_decode($eventsRaw, true);
            $events = is_array($decoded) ? $decoded : [];
        }

        $orgNameRu = trim((string) ($body['org_name_ru'] ?? $body['org_name'] ?? ''));
        $orgNameEn = trim((string) ($body['org_name_en'] ?? ''));
        $orgNameKz = trim((string) ($body['org_name_kz'] ?? ''));
        $descriptionRu = trim((string) ($body['description_ru'] ?? $body['description'] ?? ''));
        $descriptionEn = trim((string) ($body['description_en'] ?? ''));
        $descriptionKz = trim((string) ($body['description_kz'] ?? ''));

        $emptyJson = Partnership::encodeJson([]);
        $base = [
            'org_type' => $orgType,
            'country' => trim((string) ($body['country'] ?? '')),
            'city' => trim((string) ($body['city'] ?? '')),
            'website' => trim((string) ($body['website'] ?? '')),
            'contact_name' => trim((string) ($body['contact_name'] ?? '')),
            'contact_position' => trim((string) ($body['contact_position'] ?? '')),
            'contact_email' => trim((string) ($body['contact_email'] ?? '')),
            'contact_phone' => trim((string) ($body['contact_phone'] ?? '')),
            'contact_method' => trim((string) ($body['contact_method'] ?? '')),
            'cooperation_directions' => Partnership::encodeJson($coop),
            'activity_areas' => Partnership::encodeJson($areas),
            'interaction_format' => Partnership::encodeJson($format),
            'events' => Partnership::encodeJson($events),
            'data_consent' => $body['data_consent'] ?? '',
            '__form_locale' => $formLocale,
            'subtasks' => Partnership::encodeJson($projectsRu),
            'subtasks_en' => Partnership::encodeJson($projectsEn),
            'subtasks_kz' => Partnership::encodeJson($projectsKz),
        ];
        $base['org_name'] = $orgNameRu;
        $base['org_name_en'] = $orgNameEn;
        $base['org_name_kz'] = $orgNameKz;
        $base['description'] = $descriptionRu;
        $base['description_en'] = $descriptionEn;
        $base['description_kz'] = $descriptionKz;
        $base['subtasks'] = $base['subtasks'] ?? $emptyJson;
        $base['subtasks_en'] = $base['subtasks_en'] ?? $emptyJson;
        $base['subtasks_kz'] = $base['subtasks_kz'] ?? $emptyJson;
        $base['goals'] = $emptyJson;
        $base['goals_en'] = $emptyJson;
        $base['goals_kz'] = $emptyJson;

        return $base;
    }

    /**
     * При редактировании не затираем поля другой локали.
     *
     * @param array<string, mixed>|null $model
     * @param array<string, mixed> $parsed
     * @return array<string, mixed>
     */
    private function mergePartnerLocaleFieldsForSave(?array $model, array $parsed): array
    {
        unset($parsed['__form_locale']);
        if ($model === null) {
            return $parsed;
        }
        $parsed['goals'] = (string) ($model['goals'] ?? '') !== ''
            ? (string) $model['goals']
            : Partnership::encodeJson([]);
        $parsed['goals_en'] = (string) ($model['goals_en'] ?? '') !== ''
            ? (string) $model['goals_en']
            : Partnership::encodeJson([]);
        $parsed['goals_kz'] = (string) ($model['goals_kz'] ?? '') !== ''
            ? (string) $model['goals_kz']
            : Partnership::encodeJson([]);

        return $parsed;
    }

    /**
     * Модель для повторного показа формы после ошибки валидации.
     *
     * @param array<string, mixed>|null $model
     * @param array<string, mixed> $parsed
     * @param array<string, mixed> $merged
     * @return array<string, mixed>
     */
    private function partnershipFormModelForView(?array $model, array $parsed, array $merged): array
    {
        $formLocale = in_array(Lang::get(), ['ru', 'en', 'kz'], true) ? Lang::get() : 'ru';
        $empty = Partnership::encodeJson([]);
        $out = array_merge($model ?? [], $merged);
        unset($out['__form_locale'], $out['_form_locale']);

        if ($formLocale === 'en') {
            $out['org_name_en'] = (string) ($parsed['org_name_en'] ?? $out['org_name_en'] ?? '');
            $out['description_en'] = (string) ($parsed['description_en'] ?? $out['description_en'] ?? '');
            $out['subtasks_en'] = (string) ($parsed['subtasks_en'] ?? $out['subtasks_en'] ?? $empty);
            $out['goals_en'] = (string) ($parsed['goals_en'] ?? $out['goals_en'] ?? $empty);
        } elseif ($formLocale === 'kz') {
            $out['org_name_kz'] = (string) ($parsed['org_name_kz'] ?? $out['org_name_kz'] ?? '');
            $out['description_kz'] = (string) ($parsed['description_kz'] ?? $out['description_kz'] ?? '');
            $out['subtasks_kz'] = (string) ($parsed['subtasks_kz'] ?? $out['subtasks_kz'] ?? $empty);
            $out['goals_kz'] = (string) ($parsed['goals_kz'] ?? $out['goals_kz'] ?? $empty);
        } else {
            $out['org_name'] = (string) ($parsed['org_name'] ?? $out['org_name'] ?? '');
            $out['description'] = (string) ($parsed['description'] ?? $out['description'] ?? '');
            $out['subtasks'] = (string) ($parsed['subtasks'] ?? $out['subtasks'] ?? $empty);
            $out['goals'] = (string) ($parsed['goals'] ?? $out['goals'] ?? $empty);
        }

        return $out;
    }

    private function partnershipValidate(array $data, bool $isCreate = false, ?string $logoPath = null): array
    {
        $errors = [];
        if (
            trim((string) ($data['org_name'] ?? '')) === ''
            && trim((string) ($data['org_name_en'] ?? '')) === ''
            && trim((string) ($data['org_name_kz'] ?? '')) === ''
        ) {
            $errors[] = Lang::t('admin_err_org_name');
        }
        if ($data['org_type'] === '') {
            $errors[] = Lang::t('admin_err_org_type');
        }
        if ($data['country'] === '') {
            $errors[] = Lang::t('admin_err_country');
        }
        if ($data['city'] === '') {
            $errors[] = Lang::t('admin_err_city');
        }
        if ($data['contact_name'] === '') {
            $errors[] = Lang::t('admin_err_contact_name');
        }
        if ($data['contact_position'] === '') {
            $errors[] = Lang::t('admin_err_contact_position');
        }
        if ($data['contact_email'] === '') {
            $errors[] = Lang::t('admin_err_email');
        }
        if ($data['contact_phone'] === '') {
            $errors[] = Lang::t('admin_err_phone');
        }
        if ($data['contact_method'] === '') {
            $errors[] = Lang::t('admin_err_contact_method');
        }
        $coop = Partnership::decodeJson($data['cooperation_directions'] ?? '');
        if ($coop === []) {
            $errors[] = Lang::t('admin_err_coop');
        }
        if (
            trim((string) ($data['description'] ?? '')) === ''
            && trim((string) ($data['description_en'] ?? '')) === ''
            && trim((string) ($data['description_kz'] ?? '')) === ''
        ) {
            $errors[] = Lang::t('admin_err_description');
        }
        $areas = Partnership::decodeJson($data['activity_areas'] ?? '');
        if ($areas === []) {
            $errors[] = Lang::t('admin_err_areas');
        }
        $format = Partnership::decodeJson($data['interaction_format'] ?? '');
        if ($format === []) {
            $errors[] = Lang::t('admin_err_format');
        }
        if (empty($data['data_consent'])) {
            $errors[] = Lang::t('admin_err_consent');
        }
        if ($isCreate && ($logoPath === null || $logoPath === '')) {
            $errors[] = Lang::t('admin_err_logo');
        }
        return $errors;
    }

    private function partnershipHandleUpload(ServerRequestInterface $request): ?string
    {
        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }
        $name = $file->getClientFilename();
        if ($name === '' || $name === null) {
            return null;
        }
        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/partnerships';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $safe = $this->buildUploadFileName($name, $dir);
        $path = $dir . '/' . $safe;
        $file->moveTo($path);
        return '/uploads/partnerships/' . $safe;
    }

    private function partnershipHandleMaterialsUpload(ServerRequestInterface $request, ?array $existing): ?string
    {
        $files = $request->getUploadedFiles();
        $body = $request->getParsedBody();
        $toRemove = is_array($body['remove_materials'] ?? null) ? $body['remove_materials'] : [];
        $toRemove = array_values(array_filter(array_map(static fn($v) => is_string($v) ? trim($v) : '', $toRemove), static fn($v) => $v !== ''));
        $hasRemovals = $toRemove !== [];

        $list = [];
        if (is_array($existing)) {
            foreach ($existing as $path) {
                if (is_string($path) && $path !== '') {
                    if (in_array($path, $toRemove, true)) {
                        $this->deletePublicUploadIfSafe($path);
                        continue;
                    }
                    $list[] = $path;
                }
            }
        }
        $materials = $files['materials'] ?? null;
        if (!is_array($materials)) {
            if ($list === []) {
                return $hasRemovals ? Partnership::encodeJson([]) : null;
            }
            return Partnership::encodeJson($list);
        }
        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/materials';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        foreach ($materials as $file) {
            if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }
            $name = $file->getClientFilename();
            if ($name === '' || $name === null) {
                continue;
            }
            $safe = $this->buildUploadFileName($name, $dir);
            $path = $dir . '/' . $safe;
            $file->moveTo($path);
            $publicPath = '/uploads/materials/' . $safe;
            if (!in_array($publicPath, $list, true)) {
                $list[] = $publicPath;
            }
        }
        if ($list === []) {
            return $hasRemovals ? Partnership::encodeJson([]) : null;
        }
        return Partnership::encodeJson($list);
    }

    private function partnershipHandleDescriptionImagesUpload(ServerRequestInterface $request, ?array $existing): ?string
    {
        $files = $request->getUploadedFiles();
        $body = $request->getParsedBody();
        $toRemove = is_array($body['remove_description_images'] ?? null) ? $body['remove_description_images'] : [];
        $toRemove = array_values(array_filter(array_map(static fn($v) => is_string($v) ? trim($v) : '', $toRemove), static fn($v) => $v !== ''));
        $hasRemovals = $toRemove !== [];

        $list = [];
        if (is_array($existing)) {
            foreach ($existing as $path) {
                if (is_string($path) && $path !== '') {
                    if (in_array($path, $toRemove, true)) {
                        $this->deletePublicUploadIfSafe($path);
                        continue;
                    }
                    $list[] = $path;
                }
            }
        }
        $uploads = $files['description_images'] ?? null;
        if (!is_array($uploads)) {
            if ($list === []) {
                return $hasRemovals ? Partnership::encodeJson([]) : null;
            }
            return Partnership::encodeJson($list);
        }
        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/description';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        foreach ($uploads as $file) {
            if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }
            $name = $file->getClientFilename();
            if ($name === '' || $name === null) {
                continue;
            }
            $mediaType = $file->getClientMediaType();
            if (!in_array($mediaType, $allowed, true)) {
                continue;
            }
            $safe = $this->buildUploadFileName($name, $dir);
            $path = $dir . '/' . $safe;
            $file->moveTo($path);
            $publicPath = '/uploads/description/' . $safe;
            if (!in_array($publicPath, $list, true)) {
                $list[] = $publicPath;
            }
        }
        if ($list === []) {
            return $hasRemovals ? Partnership::encodeJson([]) : null;
        }
        return Partnership::encodeJson($list);
    }

    private function redirect(string $routeName, array $arguments = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(Status::FOUND);
        return $response->withHeader('Location', $this->urlGenerator->generate($routeName, $arguments));
    }

    private function deletePublicUploadIfSafe(string $publicPath): void
    {
        if (!str_starts_with($publicPath, '/uploads/')) {
            return;
        }
        $root = dirname(__DIR__, 3);
        $fullPath = $root . '/public' . $publicPath;
        $real = realpath($fullPath);
        $uploadsRoot = realpath($root . '/public/uploads');
        if ($real === false || $uploadsRoot === false) {
            return;
        }
        if (!str_starts_with($real, $uploadsRoot . DIRECTORY_SEPARATOR)) {
            return;
        }
        if (is_file($real)) {
            @unlink($real);
        }
    }

    /**
     * Keeps original filename (including Cyrillic), sanitizes unsafe chars,
     * and appends numeric suffix only on collision.
     */
    private function buildUploadFileName(string $originalName, string $dir): string
    {
        $fileName = trim(str_replace(["\\", '/'], '_', $originalName));
        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $base = preg_replace('/[^\p{L}\p{N}\s._-]/u', '_', (string) $base);
        $base = preg_replace('/\s+/u', ' ', (string) $base);
        $base = trim((string) $base);
        if ($base === '') {
            $base = 'file';
        }

        $ext = preg_replace('/[^\p{L}\p{N}]/u', '', (string) $ext);
        $candidate = $ext !== '' ? ($base . '.' . $ext) : $base;
        $counter = 1;

        while (is_file($dir . '/' . $candidate)) {
            $counter++;
            $candidate = $ext !== '' ? ($base . '_' . $counter . '.' . $ext) : ($base . '_' . $counter);
        }

        return $candidate;
    }

    /**
     * @return list<array{name:string,description:string,goals:list<string>,subtasks:list<string>,ready:string,images:list<string>}>
     */
    private function parseProjectsJson(string $projectsRaw): array
    {
        $projectsRaw = trim($projectsRaw);
        if ($projectsRaw === '') {
            return [];
        }
        $decodedProjects = json_decode($projectsRaw, true);
        if (!is_array($decodedProjects)) {
            return [];
        }
        $projects = [];
        foreach ($decodedProjects as $project) {
            if (!is_array($project)) {
                continue;
            }
            $name = trim((string) ($project['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $desc = trim((string) ($project['description'] ?? ''));
            $status = trim((string) ($project['ready'] ?? ''));
            $projectGoals = [];
            if (is_array($project['goals'] ?? null)) {
                foreach ($project['goals'] as $goal) {
                    $goal = trim((string) $goal);
                    if ($goal !== '') {
                        $projectGoals[] = $goal;
                    }
                }
            }
            $projectSubtasks = [];
            if (is_array($project['subtasks'] ?? null)) {
                foreach ($project['subtasks'] as $subtask) {
                    $subtask = trim((string) $subtask);
                    if ($subtask !== '') {
                        $projectSubtasks[] = $subtask;
                    }
                }
            }
            $projectImages = [];
            if (is_array($project['images'] ?? null)) {
                foreach ($project['images'] as $img) {
                    $img = trim((string) $img);
                    if ($img !== '' && str_starts_with($img, '/uploads/projects/')) {
                        $projectImages[] = $img;
                    }
                }
            }
            $projects[] = [
                'name' => $name,
                'description' => $desc,
                'goals' => $projectGoals,
                'subtasks' => $projectSubtasks,
                'ready' => $status,
                'images' => $projectImages,
            ];
        }
        return $projects;
    }
}
