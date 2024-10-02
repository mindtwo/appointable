<?php

use mindtwo\Appointable\Services\Appointable;

it('registers the appointable service', function () {
    $service = app(Appointable::class);

    expect($service)->not->toBeNull();
});

it('registers an alias for the appointable service', function () {
    $service = app('appointable');

    expect($service)->not->toBeNull();
});

it('can register routes via the service', function () {
    // @see \mindtwo\Appointable\Services\Appointable::routes()
    // @link workbench/routes/web.php
    $routes = collect(app('router')->getRoutes()->getRoutesByName());

    expect($routes->has('appointments.index'))->toBeTrue();
    expect($routes->has('appointments.store'))->toBeTrue();

    expect($routes->has('appointments.update'))->toBeTrue();
    expect($routes->has('appointments.cancel'))->toBeTrue();

    expect($routes->has('appointments.confirm'))->toBeTrue();
    expect($routes->has('appointments.decline'))->toBeTrue();
});
