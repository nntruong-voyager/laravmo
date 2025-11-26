<?php

use Illuminate\Support\Facades\Route;

Route::view('/orders/docs', 'orders::index')->name('orders.docs');
