<?php

namespace mindtwo\Appointable\Contracts;

use Illuminate\Support\Carbon;

interface BaseAppointable
{
    /**
     * Get a unique identifier for the appointment.
     */
    public function getAppointmentUid(): string;

    /**
     * Get the sequence of the appointment.
     * The sequence should be incremented every time the appointment is updated.
     */
    public function getSequence(): int;

    /**
     * Get the id of the invitee.
     */
    public function getInviteeId(): ?int;

    /**
     * Get the title of the appointment.
     */
    public function getAppointmentTitle(): ?string;

    /**
     * Get the description of the appointment.
     */
    public function getAppointmentDescription(): ?string;

    /**
     * Get the start date with time of the appointment.
     */
    public function getAppointmentStart(): Carbon;

    /**
     * Get the end date with time of the appointment.
     */
    public function getAppointmentEnd(): ?Carbon;
}
