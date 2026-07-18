<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Syriable\UserContext\Http\Controllers\ContextController;
use Syriable\UserContext\Http\Controllers\HeartbeatController;

if ((bool) config('user-context.heartbeat.enabled', true)) {
    Route::post('heartbeat', HeartbeatController::class)->name('heartbeat');
}

if ((bool) config('user-context.routes.expose_me', true)) {
    Route::get('me', ContextController::class)->name('me');
}
