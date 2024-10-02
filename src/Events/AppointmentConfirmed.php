<?php

namespace mindtwo\Appointable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use mindtwo\Appointable\Models\Appointment;

class AppointmentConfirmed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {}
}