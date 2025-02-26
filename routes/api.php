<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/posts', function (Request $request) {
    return Auth::user();
})->middleware(['oauth']);
