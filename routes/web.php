<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\OAuthClientController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('welcome');
})->middleware(['auth','oauth.client']);

Route::get('/login', [OAuthController::class, 'login'])->name('login');
Route::get('/logout', [OAuthController::class, 'logout'])->name('logout');

Route::get('/oauth/callback', [OAuthController::class, 'callback']);
Route::get('/ssologin', [OAuthController::class, 'ssologin']);

Route::get('/user/sync', [UserController::class, 'syncUser']);
