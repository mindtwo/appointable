<?php

namespace mindtwo\Appointable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use mindtwo\Appointable\Models\Appointment;

class AppointmentUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<string,  mixed>  $changes
     * @param  array<string,  mixed>  $original
     */
    public function __construct(
        public Appointment $appointment,
        public array $changes,
        public array $original,
    ) {}
}
