<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Events\AppointmentCanceled;
use mindtwo\Appointable\Models\Appointment;

class CancelAppointment
{
    public function __invoke(Appointment $appointment, bool $force = false): void
    {
        if (! $force && $appointment->status !== null) {
            return;
        }

        $appointment->delete();

        // dispatch event
        AppointmentCanceled::dispatch($appointment);
    }
}
