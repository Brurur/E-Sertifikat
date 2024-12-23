<?php

use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email-form', function () {
    return view('email-form');
});

Route::post('/send-certificate', [MailController::class, 'sendCertificate'])->name('send.certificate');

Route::get('/', [QrCodeController::class, 'show']);