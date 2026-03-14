<?php

declare(strict_types=1);

use App\Controller\LandingController;
use App\Controller\LogoController;
use App\Controller\ServeController;
use App\Controller\Admin\AdminController;
use App\Middleware\AuthMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()->routes(
        Route::get('/')->action([LandingController::class, 'index'])->name('home'),
        Route::get('/card/{id:\d+}')->action([LandingController::class, 'view'])->name('card-view'),
        Route::get('/logo/{name}')->action([LogoController::class, 'file'])->name('logo'),
        Route::get('/serve/partnership')->action([ServeController::class, 'partnership'])->name('serve/partnership'),
        Route::get('/admin/login')->action([AdminController::class, 'login'])->name('admin/login'),
        Route::post('/admin/login')->action([AdminController::class, 'loginPost'])->name('admin/login-post'),
        Route::get('/admin/logout')->action([AdminController::class, 'logout'])->name('admin/logout'),
    ),
    Group::create('/admin')->middleware(AuthMiddleware::class)->routes(
        Route::get('/dashboard')->action([AdminController::class, 'dashboard'])->name('admin/dashboard'),
        Route::get('/partnerships')->action([AdminController::class, 'partnershipsIndex'])->name('admin/partnerships'),
        Route::get('/partnerships/create')->action([AdminController::class, 'partnershipCreate'])->name('admin/partnerships/create'),
        Route::post('/partnerships/create')->action([AdminController::class, 'partnershipCreatePost'])->name('admin/partnerships/create-post'),
        Route::get('/partnerships/{id:\d+}/edit')->action([AdminController::class, 'partnershipEdit'])->name('admin/partnerships/edit'),
        Route::post('/partnerships/{id:\d+}/edit')->action([AdminController::class, 'partnershipEditPost'])->name('admin/partnerships/edit-post'),
        Route::post('/partnerships/{id:\d+}/delete')->action([AdminController::class, 'partnershipDelete'])->name('admin/partnerships/delete'),
    ),
];
