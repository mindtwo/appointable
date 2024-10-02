<?php

namespace mindtwo\Appointable\Actions;

use mindtwo\Appointable\Contracts\BaseAppointable as AppointableContract;
use mindtwo\Appointable\Contracts\LocatableAppointment;
use mindtwo\Appointable\Events\AppointmentUpdated;
use mindtwo\Appointable\Models\Appointment;

class UpdateLinkedAppointment
{
    public function __invoke(AppointableContract $appointable): ?Appointment
    {
        $appointment = Appointment::query()
            ->where('uid', $appointable->getAppointmentUid())
            ->with('linkable')
            ->first();

        if ($appointment === null) {
            // check if we maybe have to link the appointment
            return null;
        }

        // Get the appointable data
        $appointableData = $this->getAppointableArray($appointable);
        $oldAppointmentData = $appointment->toAppointmentData();

        $appointmentData = array_intersect_key($oldAppointmentData, $appointableData);

        // get the difference between the old and new data of the appointment
        $diff = array_diff_assoc($appointableData, $appointmentData);

        // update the appointment
        $result = tap($appointment)->update($diff);

        // dispatch event
        AppointmentUpdated::dispatch($appointment, $diff, $oldAppointmentData);

        return $result;
    }

    /**
     * Get the appointable data array.
     *
     *
     * @return array<string, mixed>
     */
    protected function getAppointableArray(AppointableContract $appointable): array
    {
        $startDate = $appointable->getAppointmentStart();
        $endDate = $appointable->getAppointmentEnd();

        // If no end date is set, the end date is the same as the start date
        if (! $endDate) {
            $endDate = $startDate;
        }

        // Get the time and date from the start and end date
        $startTime = $startDate->format('H:i:s');
        $endTime = $endDate->format('H:i:s');

        // Create the data array
        $data = [
            'title' => $appointable->getAppointmentTitle(),
            'description' => $appointable->getAppointmentDescription(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($appointable instanceof LocatableAppointment && $appointable->hasLocation()) {
            $data['location'] = $appointable->getLocation();
        }

        return $data;
    }
}
