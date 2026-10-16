<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadExportController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('leads.index');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads/visiting-card', [LeadController::class, 'readCard'])->name('leads.visiting-card');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/import', [LeadImportController::class, 'create'])->name('leads.import');
    Route::post('/leads/import', [LeadImportController::class, 'store'])->name('leads.import.store');
    Route::get('/leads/template', [LeadImportController::class, 'template'])->name('leads.template');
    Route::match(['get', 'post'], '/leads/export', [LeadExportController::class, 'download'])->name('leads.export');
    Route::post('/leads/bulk-destroy', [LeadController::class, 'bulkDestroy'])->name('leads.bulk-destroy');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::post('/departments/{department}/toggle', [DepartmentController::class, 'toggle'])->name('departments.toggle');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/settings/statuses', [SettingsController::class, 'storeStatus'])->name('statuses.store');
    Route::put('/settings/statuses/{status}', [SettingsController::class, 'updateStatus'])->name('statuses.update');
    Route::delete('/settings/statuses/{status}', [SettingsController::class, 'destroyStatus'])->name('statuses.destroy');
});

// Campaign Creation Pages (matches screenshot mockups)
Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
Route::get('/campaign/create', [CampaignController::class, 'create']);
Route::match(['get', 'post'], '/campaigns/email-content', [CampaignController::class, 'emailContent'])->name('campaigns.email-content');
Route::match(['get', 'post'], '/campaign/email-content', [CampaignController::class, 'emailContent']);
Route::match(['get', 'post'], '/campaigns/review', [CampaignController::class, 'review'])->name('campaigns.review');
Route::match(['get', 'post'], '/campaign/review', [CampaignController::class, 'review']);
Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
Route::post('/campaigns/upload', [CampaignController::class, 'upload'])->name('campaigns.upload');


