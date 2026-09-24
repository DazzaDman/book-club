<?php

use App\Http\Controllers\ClubController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::inertia('clubs/create', 'clubs/create')->name('clubs.create');
    Route::post('clubs', [ClubController::class, 'store'])->name('clubs.store');
});

require __DIR__.'/settings.php';
