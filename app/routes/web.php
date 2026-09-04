<?php

use App\Http\Controllers\ProductProjectController;
use App\Http\Controllers\ProductSourceController;
use App\Http\Controllers\ProductSkuController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\CreativeAssetController;
use App\Http\Controllers\CampaignTestController;
use App\Http\Controllers\OptimizationFeedbackController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectWorkflowController;
use App\Http\Controllers\ProjectWorkspaceController;
use App\Http\Controllers\FeedbackCenterController;
use App\Http\Controllers\ResearchSourceController;
use App\Http\Controllers\ProjectDecisionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? to_route('projects.index') : to_route('login'));
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/feedback', [FeedbackCenterController::class, 'index'])->name('feedback.index');
    Route::get('/projects', [ProductProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}/workspace', [ProjectWorkspaceController::class, 'show'])->name('projects.workspace');
    Route::get('/projects/{project}', [ProductProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects', [ProductProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{project}/submit', [ProjectWorkflowController::class, 'submit'])->name('projects.submit');
    Route::post('/projects/{project}/research-sources', [ResearchSourceController::class, 'store'])->name('projects.research-sources.store');
    Route::post('/projects/{project}/decisions', [ProjectDecisionController::class, 'store'])->name('projects.decisions.store');
    Route::post('/projects/{project}/sources', [ProductSourceController::class, 'store'])->name('projects.sources.store');
    Route::post('/projects/{project}/skus', [ProductSkuController::class, 'store'])->name('projects.skus.store');
    Route::post('/projects/{project}/landing-pages', [LandingPageController::class, 'store'])->name('projects.landing-pages.store');
    Route::post('/projects/{project}/creative-assets', [CreativeAssetController::class, 'store'])->name('projects.creative-assets.store');
    Route::post('/projects/{project}/campaign-tests', [CampaignTestController::class, 'store'])->name('projects.campaign-tests.store');
    Route::patch('/projects/{project}/optimization-feedback/{feedback}', [OptimizationFeedbackController::class, 'update'])->name('projects.optimization-feedback.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
});
