<?php

use Illuminate\Support\Facades\Route;

Route::view('/users/docs', 'users::index')->name('users.docs');
