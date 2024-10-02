<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Events\AppointmentCanceled;
use mindtwo\Appointable\Models\Appointment;

class CancelAppointment
{
    public function __invoke(Appointment $appointment): void
    {
        $appointment->delete();

        // dispatch event
        AppointmentCanceled::dispatch($appointment);
    }
}
