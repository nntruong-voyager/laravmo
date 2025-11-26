<?php

use Illuminate\Support\Facades\Route;

Route::view('/payments/docs', 'payments::index')->name('payments.docs');
