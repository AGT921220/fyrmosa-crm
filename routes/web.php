<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->to(auth()->user()->homeRoute())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);

    Route::resource('clients', ClientController::class)->except(['show']);

    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/status', [QuotationController::class, 'status'])->name('quotations.status');
    Route::post('quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::post('quotations/{quotation}/versions', [QuotationController::class, 'version'])->name('quotations.versions.store');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::post('quotations/{quotation}/follow-ups', [QuotationController::class, 'followUp'])->name('quotations.follow-ups.store');
    Route::post('quotations/{quotation}/attachments', [QuotationController::class, 'attachment'])->name('quotations.attachments.store');
    Route::resource('quotations', QuotationController::class)->except(['destroy']);

    Route::get('orders/{order}/pdf', [OrderController::class, 'pdf'])->name('orders.pdf');
    Route::post('orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');
    Route::post('orders/{order}/deliveries', [OrderController::class, 'delivery'])->name('orders.deliveries.store');
    Route::post('orders/{order}/follow-ups', [OrderController::class, 'followUp'])->name('orders.follow-ups.store');
    Route::post('orders/{order}/attachments', [OrderController::class, 'attachment'])->name('orders.attachments.store');
    Route::resource('orders', OrderController::class)->except(['destroy']);

    Route::get('attachments/{attachment}', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
});
