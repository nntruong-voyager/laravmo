<?php

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

foreach (Module::allEnabled() as $module) {
    $path = module_path($module->getName(), 'routes/api.php');

    if (file_exists($path)) {
        Route::group([], $path);
    }
}

