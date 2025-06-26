<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ScheduledMessageController;
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


Route::get('teste', function () {
    User::first()->notify(new WhatsappNotification());
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Novas rotas para as seções do app
    Route::resource('contacts', ContactController::class)->except(['show']);
    Route::resource('billings', BillingController::class);
    Route::patch('/installments/{installment}/toggle-status', [InstallmentController::class, 'toggleStatus'])->name('installments.toggleStatus');
    Route::resource('messages', MessageController::class)->only(['index', 'show']);
    Route::post('/messages/{scheduledMessage}/send-now', [ScheduledMessageController::class, 'sendNow'])
        ->name('messages.sendNow');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
