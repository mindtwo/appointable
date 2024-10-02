<?php

namespace mindtwo\Appointable\Services;

use Illuminate\Support\Facades\Route;
use mindtwo\Appointable\Http\Controllers\CancelAppointmentController;
use mindtwo\Appointable\Http\Controllers\ConfirmInvitationController;
use mindtwo\Appointable\Http\Controllers\CreateAppointmentController;
use mindtwo\Appointable\Http\Controllers\DeclineInvitationController;
use mindtwo\Appointable\Http\Controllers\IndexAppointablesController;
use mindtwo\Appointable\Http\Controllers\UpdateAppointmentController;

class Appointable
{
    /**
     * Register the appointable routes.
     *
     * @param  null|string|array<string>  $middleware
     */
    public function routes(string $prefix = 'appointments', null|string|array $middleware = null): void
    {
        $middleware = $middleware ?? config('appointable.middleware');

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group(function () {
                // Index all appointables
                Route::get('/', IndexAppointablesController::class)->name('appointments.index');

                // routes for appointment management - base / default appointments
                Route::post('/', CreateAppointmentController::class)->name('appointments.store');
                Route::match(['put', 'patch'], '/{uuidOrUid}', UpdateAppointmentController::class)->name('appointments.update');
                Route::delete('/{uuidOrUid}', CancelAppointmentController::class)->name('appointments.cancel');

                // routes for appointment invitation management
                Route::post('/{uuidOrUid}/confirm', ConfirmInvitationController::class)->name('appointments.confirm');
                Route::post('/{uuidOrUid}/decline', DeclineInvitationController::class)->name('appointments.decline');
            });
    }
}
