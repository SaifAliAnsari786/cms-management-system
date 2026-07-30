<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // User Routes

    Route::prefix('users')->group(function () {

        Route::middleware('permission:user-list')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{user}', [UserController::class, 'show']);
        });

        Route::middleware('permission:user-create')->group(function () {
            Route::post('/', [UserController::class, 'store']);
        });

        Route::middleware('permission:user-edit')->group(function () {
            Route::put('/{user}', [UserController::class, 'update']);
        });

        Route::middleware('permission:user-delete')->group(function () {
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });

    });

    // Page Routes

    Route::prefix('pages')->group(function () {

        Route::middleware('permission:page-list')->group(function () {
            Route::get('/', [PageController::class, 'index']);
            Route::get('/trash', [PageController::class, 'trash']);
            Route::get('/{page}', [PageController::class, 'show']);
        });

        Route::middleware('permission:page-create')->group(function () {
            Route::post('/', [PageController::class, 'store']);
        });

        Route::middleware('permission:page-edit')->group(function () {
            Route::put('/{page}', [PageController::class, 'update']);
        });

        Route::middleware('permission:page-delete')->group(function () {
            Route::delete('/{page}', [PageController::class, 'destroy']);
        });

    });

    // Menu Routes

    Route::prefix('menus')->group(function () {

        Route::middleware('permission:menu-list')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('/{menu}', [MenuController::class, 'show']);
        });

        Route::middleware('permission:menu-create')->group(function () {
            Route::post('/', [MenuController::class, 'store']);
        });

        Route::middleware('permission:menu-edit')->group(function () {
            Route::put('/{menu}', [MenuController::class, 'update']);
        });

        Route::middleware('permission:menu-delete')->group(function () {
            Route::delete('/{menu}', [MenuController::class, 'destroy']);
        });

    });


});
