<?php

use Illuminate\Support\Facades\Route;
use mindtwo\Appointable\Facades\Appointable;

Route::get('/', function () {
    return view('welcome');
});

Appointable::routes(middleware: ['auth']);
