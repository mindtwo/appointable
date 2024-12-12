<?php

namespace mindtwo\Appointable\Contracts;

use mindtwo\Appointable\Enums\AppointmentStatus;

interface HandlesAppointmentStatus
{
    /**
     * Get the appointment status for creation.
     *
     * Return null if the appointment should not handle a status.
     */
    public function getBaseAppointmentStatus(): ?AppointmentStatus;
}
