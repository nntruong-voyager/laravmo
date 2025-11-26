<?php

use Illuminate\Support\Facades\Route;

Route::view('/inventory/docs', 'inventory::index')->name('inventory.docs');
