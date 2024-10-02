<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Events\AppointmentDeclined;
use mindtwo\Appointable\Models\Appointment;

class DeclineAppointmentInvitation
{
    public function __invoke(Appointment $appointment): bool
    {
        $appointment->status = AppointmentStatus::Declined;
        $result = $appointment->save();

        // dispatch event
        AppointmentDeclined::dispatch($appointment);

        return $result;
    }
}