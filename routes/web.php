<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OAuthClientController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/sync', [UserController::class, 'syncUser']);
