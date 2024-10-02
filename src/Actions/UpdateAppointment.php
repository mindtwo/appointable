<?php

namespace mindtwo\Appointable\Actions;

use Illuminate\Support\Carbon;
use mindtwo\Appointable\Events\AppointmentUpdated;
use mindtwo\Appointable\Models\Appointment;

class UpdateAppointment
{
    /**
     * Update an appointment.
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Appointment $appointment, array $data): Appointment
    {
        $appointmentData = $appointment->toAppointmentData();

        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date'])->setTime(0, 0, 0) : $appointmentData['end_date'];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date'])->setTime(0, 0, 0) : $appointmentData['start_date'];

        if ($data['is_entire_day'] ?? false) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $oldAppointmentData = $appointmentData;

        $updateData = [
            'title' => $data['title'] ?? $appointment->title,
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'is_entire_day' => $data['is_entire_day'] ?? false,
            // time and date
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ];

        // get the difference between the old and new data of the appointment
        $diff = array_diff_assoc($updateData, $appointmentData);

        // update the appointment
        $result = tap($appointment)->update($diff);

        // dispatch event
        AppointmentUpdated::dispatch($appointment, $updateData, $oldAppointmentData);

        return $result;
    }
}
