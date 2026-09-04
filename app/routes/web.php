<?php

use App\Http\Controllers\ProductProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/projects', [ProductProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProductProjectController::class, 'store'])->name('projects.store');
});
