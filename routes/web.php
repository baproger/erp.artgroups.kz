<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));
Route::redirect('/home', '/dashboard');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard',            [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live-stats', [DashboardController::class, 'liveStats'])->name('dashboard.live');

    // Напоминания о незаполненных фактах (для колокольчика в шапке)
    Route::get('/notifications/unfilled-facts', [NotificationController::class, 'unfilledFacts'])->name('notifications.unfilled');

    // KPI per department
    Route::get('/branches/{branch}',               [BranchController::class, 'show'])->name('branch.view');
    Route::get('/departments/{department}',        [KpiController::class, 'departmentView'])->name('department.view');
    Route::post('/kpis/{kpi}/fact',               [KpiController::class, 'storeFact'])->name('kpi.fact.store');
    Route::get('/kpis/{kpi}/history',             [KpiController::class, 'factHistory'])->name('kpi.history');
    Route::post('/kpis/{kpi}/plan',               [KpiController::class, 'updatePlan'])->name('kpi.plan.update');
    Route::post('/departments/{department}/plans', [KpiController::class, 'updatePlans'])->name('department.plans.update');
    Route::put('/facts/{fact}',                  [KpiController::class, 'updateFact'])->name('kpi.fact.update');
    Route::delete('/facts/{fact}',               [KpiController::class, 'destroyFact'])->name('kpi.fact.destroy');

    // Recommendations
    Route::get('/recommendations',                            [RecommendationController::class, 'index'])->name('rec.index');
    Route::post('/recommendations/{recommendation}/dismiss', [RecommendationController::class, 'dismiss'])->name('rec.dismiss');
    Route::post('/recommendations/generate',                  [RecommendationController::class, 'generate'])->name('rec.generate');

    // Excel export
    Route::get('/export', [ExportController::class, 'download'])->name('export.download');

    // Profile
    Route::get('/profile',              [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile',              [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',     [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar',      [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar',    [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // Settings (CEO only)
    Route::get('/settings',          [SettingsController::class, 'edit'])->name('settings');
    Route::post('/settings',         [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/logo',  [SettingsController::class, 'destroyLogo'])->name('settings.logo.destroy');

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users',                  [AdminController::class, 'users'])->name('users');
        Route::post('/users',                 [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}',           [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}',        [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/branches',              [AdminController::class, 'storeBranch'])->name('branches.store');
        Route::put('/branches/{branch}',      [AdminController::class, 'updateBranch'])->name('branches.update');
        Route::delete('/branches/{branch}',   [AdminController::class, 'destroyBranch'])->name('branches.destroy');
    });

});
