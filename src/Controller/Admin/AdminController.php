<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Partnership;
use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        return $this->view->render('admin/partnerships/form', ['model' => null, 'errors' => []]);
    }

    public function partnershipCreatePost(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->partnershipDataFromRequest($request);
        $filePath = $this->partnershipHandleUpload($request);
        if ($filePath !== null) {
            $data['file_path'] = $filePath;
        }
        $errors = $this->partnershipValidate($data, true, $data['file_path'] ?? null);
        if ($errors !== []) {
            return $this->view->render('admin/partnerships/form', ['model' => $data, 'errors' => $errors]);
        }
        $materialsJson = $this->partnershipHandleMaterialsUpload($request, null);
        if ($materialsJson !== null) {
            $data['materials'] = $materialsJson;
        }
        $descImagesJson = $this->partnershipHandleDescriptionImagesUpload($request, null);
        if ($descImagesJson !== null) {
            $data['description_images'] = $descImagesJson;
        }
        $data['data_consent'] = !empty($data['data_consent']) ? 1 : 0;
        $data['published'] = 1;
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $this->db->createCommand()->insert('{{%partnership}}', $data)->execute();
        return $this->redirect('admin/partnerships');
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
        $data = $this->partnershipDataFromRequest($request);
        $filePath = $this->partnershipHandleUpload($request);
        if ($filePath !== null) {
            $data['file_path'] = $filePath;
        }
        $data['file_path'] = $data['file_path'] ?? $model['file_path'] ?? null;
        $errors = $this->partnershipValidate($data, false, $data['file_path']);
        if ($errors !== []) {
            return $this->view->render('admin/partnerships/form', ['model' => array_merge($model, $data), 'errors' => $errors]);
        }
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

    private function partnershipDataFromRequest(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody() ?? [];
        $coop = is_array($body['cooperation_directions'] ?? null) ? $body['cooperation_directions'] : [];
        $areas = is_array($body['activity_areas'] ?? null) ? $body['activity_areas'] : [];
        $format = is_array($body['interaction_format'] ?? null) ? $body['interaction_format'] : [];
        $orgType = trim((string) ($body['org_type'] ?? ''));
        if ($orgType === 'other') {
            $orgType = trim((string) ($body['org_type_other'] ?? ''));
        }
        $coopOther = trim((string) ($body['cooperation_directions_other'] ?? ''));
        if ($coopOther !== '') {
            $coop[] = $coopOther;
        }
        $areasOther = trim((string) ($body['activity_areas_other'] ?? ''));
        if ($areasOther !== '') {
            $areas[] = $areasOther;
        }
        $formatOther = trim((string) ($body['interaction_format_other'] ?? ''));
        if ($formatOther !== '') {
            $format[] = $formatOther;
        }
        $subtasksRaw = trim((string) ($body['subtasks'] ?? ''));
        $subtasks = $subtasksRaw !== '' ? array_values(array_filter(array_map('trim', explode("\n", $subtasksRaw)))) : [];
        
        $goalsRaw = trim((string) ($body['goals'] ?? ''));
        $goals = $goalsRaw !== '' ? array_values(array_filter(array_map('trim', explode("\n", $goalsRaw)))) : [];

        $eventsRaw = trim((string) ($body['events'] ?? ''));
        $events = [];
        if ($eventsRaw !== '') {
            $decoded = json_decode($eventsRaw, true);
            $events = is_array($decoded) ? $decoded : [];
        }

        return [
            'org_name' => trim((string) ($body['org_name'] ?? '')),
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
            'description' => trim((string) ($body['description'] ?? '')),
            'activity_areas' => Partnership::encodeJson($areas),
            'interaction_format' => Partnership::encodeJson($format),
            'subtasks' => Partnership::encodeJson($subtasks),
            'goals' => Partnership::encodeJson($goals),
            'events' => Partnership::encodeJson($events),
            'data_consent' => $body['data_consent'] ?? '',
        ];
    }

    private function partnershipValidate(array $data, bool $isCreate = false, ?string $logoPath = null): array
    {
        $errors = [];
        if ($data['org_name'] === '') {
            $errors[] = 'Название организации обязательно.';
        }
        if ($data['org_type'] === '') {
            $errors[] = 'Укажите тип организации.';
        }
        if ($data['country'] === '') {
            $errors[] = 'Укажите страну.';
        }
        if ($data['city'] === '') {
            $errors[] = 'Укажите город.';
        }
        if ($data['contact_name'] === '') {
            $errors[] = 'Имя контактного лица обязательно.';
        }
        if ($data['contact_position'] === '') {
            $errors[] = 'Укажите должность контактного лица.';
        }
        if ($data['contact_email'] === '') {
            $errors[] = 'Email обязателен.';
        }
        if ($data['contact_phone'] === '') {
            $errors[] = 'Укажите телефон.';
        }
        if ($data['contact_method'] === '') {
            $errors[] = 'Укажите предпочитаемый способ связи.';
        }
        $coop = Partnership::decodeJson($data['cooperation_directions'] ?? '');
        if ($coop === []) {
            $errors[] = 'Выберите хотя бы одно направление сотрудничества.';
        }
        if (trim((string) ($data['description'] ?? '')) === '') {
            $errors[] = 'Заполните описание.';
        }
        $areas = Partnership::decodeJson($data['activity_areas'] ?? '');
        if ($areas === []) {
            $errors[] = 'Выберите хотя бы одну область деятельности.';
        }
        $format = Partnership::decodeJson($data['interaction_format'] ?? '');
        if ($format === []) {
            $errors[] = 'Выберите хотя бы один формат взаимодействия.';
        }
        if (empty($data['data_consent'])) {
            $errors[] = 'Необходимо согласие на обработку данных.';
        }
        if ($isCreate && ($logoPath === null || $logoPath === '')) {
            $errors[] = 'Загрузите логотип организации.';
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
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename) . '_' . time() . '.' . $ext;
        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/partnerships';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $path = $dir . '/' . $safe;
        $file->moveTo($path);
        return '/uploads/partnerships/' . $safe;
    }

    private function partnershipHandleMaterialsUpload(ServerRequestInterface $request, ?array $existing): ?string
    {
        $files = $request->getUploadedFiles();
        $list = [];
        if (is_array($existing)) {
            foreach ($existing as $path) {
                if (is_string($path) && $path !== '') {
                    $list[] = $path;
                }
            }
        }
        $materials = $files['materials'] ?? null;
        if (!is_array($materials)) {
            return $list === [] ? null : Partnership::encodeJson($list);
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
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $basename = pathinfo($name, PATHINFO_FILENAME);
            $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename) . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext !== '' ? '.' . $ext : '');
            $path = $dir . '/' . $safe;
            $file->moveTo($path);
            $publicPath = '/uploads/materials/' . $safe;
            if (!in_array($publicPath, $list, true)) {
                $list[] = $publicPath;
            }
        }
        return $list === [] ? null : Partnership::encodeJson($list);
    }

    private function partnershipHandleDescriptionImagesUpload(ServerRequestInterface $request, ?array $existing): ?string
    {
        $files = $request->getUploadedFiles();
        $list = [];
        if (is_array($existing)) {
            foreach ($existing as $path) {
                if (is_string($path) && $path !== '') {
                    $list[] = $path;
                }
            }
        }
        $uploads = $files['description_images'] ?? null;
        if (!is_array($uploads)) {
            return $list === [] ? null : Partnership::encodeJson($list);
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
            $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
            $basename = pathinfo($name, PATHINFO_FILENAME);
            $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $path = $dir . '/' . $safe;
            $file->moveTo($path);
            $publicPath = '/uploads/description/' . $safe;
            if (!in_array($publicPath, $list, true)) {
                $list[] = $publicPath;
            }
        }
        return $list === [] ? null : Partnership::encodeJson($list);
    }

    private function redirect(string $routeName, array $arguments = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(Status::FOUND);
        return $response->withHeader('Location', $this->urlGenerator->generate($routeName, $arguments));
    }
}
