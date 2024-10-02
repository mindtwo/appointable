<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Events\AppointmentCreated;
use mindtwo\Appointable\Models\Appointment;

class CreateAppointment
{
    /**
     * Create an appointment.
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data, int $inviteeId, bool $isEntireDay = false): Appointment
    {
        if ($isEntireDay) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $appointment = Appointment::create([
            'title' => $data['title'],
            'invitee_id' => $inviteeId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'is_entire_day' => $isEntireDay,
        ]);

        // dispatch event
        AppointmentCreated::dispatch($appointment);

        return $appointment;
    }
}
