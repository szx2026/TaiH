<?php

use App\Http\Controllers\ProductProjectController;
use App\Http\Controllers\ProductSourceController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProjectWorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/projects', [ProductProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProductProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects', [ProductProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{project}/submit', [ProjectWorkflowController::class, 'submit'])->name('projects.submit');
    Route::post('/projects/{project}/sources', [ProductSourceController::class, 'store'])->name('projects.sources.store');
    Route::post('/projects/{project}/landing-pages', [LandingPageController::class, 'store'])->name('projects.landing-pages.store');
});
