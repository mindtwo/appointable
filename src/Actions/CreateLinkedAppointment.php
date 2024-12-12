<?php

namespace mindtwo\Appointable\Actions;

use Illuminate\Database\Eloquent\Model;
use mindtwo\Appointable\Contracts\BaseAppointable as AppointableContract;
use mindtwo\Appointable\Contracts\HandlesAppointmentStatus;
use mindtwo\Appointable\Contracts\LocatableAppointment;
use mindtwo\Appointable\Contracts\MaybeIsEntireDay;
use mindtwo\Appointable\Events\AppointmentCreated;
use mindtwo\Appointable\Models\Appointment;

class CreateLinkedAppointment
{
    public function __invoke(AppointableContract $appointable, bool $silent = false): ?Appointment
    {
        $invitee = $appointable->getInvitee();
        if (! $invitee) {
            return null;
        }

        // Create the data array
        $data = $this->getAppointableArray($appointable);

        // If the appointable is a model, set the linkable id and type
        if ($appointable instanceof Model) {
            $data['linkable_id'] = $appointable->getKey();
            $data['linkable_type'] = $appointable->getMorphClass();
        }

        // Create the appointment
        $appointment = Appointment::create($data);

        // dispatch event
        if (! $silent) {
            AppointmentCreated::dispatch($appointment);
        }

        return $appointment;
    }

    /**
     * Get the appointable array.
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

        $invitee = $appointable->getInvitee();

        // Create the data array
        $data = [
            'uid' => $appointable->getAppointmentUid(),
            'title' => $appointable->getAppointmentTitle(),
            'description' => $appointable->getAppointmentDescription(),
            'invitee_id' => $invitee->getKey(),
            'invitee_type' => $invitee->getMorphClass(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($appointable instanceof HandlesAppointmentStatus) {
            $data['status'] = $appointable->getBaseAppointmentStatus();
        }

        if ($appointable instanceof MaybeIsEntireDay) {
            $data['is_entire_day'] = $appointable->isEntireDay();
        }

        if ($appointable instanceof LocatableAppointment && $appointable->hasLocation()) {
            $data['location'] = $appointable->getLocation();
        }

        return $data;
    }
}
