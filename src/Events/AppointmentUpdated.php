<?php

namespace mindtwo\Appointable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
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

    public function getDiffKeys(): array
    {
        return array_keys($this->getDiff());
    }

    public function getDiff(): array
    {
        $changes = collect($this->changes)->map(function ($value, $key) {
            if ($value instanceof Carbon) {
                return $value->format('Y-m-d H:i:s');
            }

            return $value;
        })->toArray();

        $original = collect($this->original)->map(function ($value, $key) {
            if ($value instanceof Carbon) {
                return $value->format('Y-m-d H:i:s');
            }

            return $value;
        })->toArray();

        return array_diff_assoc($changes, $original);
    }
}
