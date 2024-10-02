<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Events\AppointmentConfirmed;
use mindtwo\Appointable\Models\Appointment;

class ConfirmAppointmentInvitation
{
    public function __invoke(Appointment $appointment): bool
    {
        $appointment->status = AppointmentStatus::Confirmed;
        $result = $appointment->save();

        // dispatch event
        AppointmentConfirmed::dispatch($appointment);

        return $result;
    }
}
