<?php

use App\Models\User;
use App\Notifications\WhatsappNotification;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('teste', function () {
   User::first()->notify(new WhatsappNotification());
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
