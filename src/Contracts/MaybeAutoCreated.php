<?php

namespace mindtwo\Appointable\Contracts;

interface MaybeAutoCreated
{
    /**
     * Determine if the appointment creation is skipped.
     *
     * @return bool - Return false to skip the appointment creation.
     */
    public function autoCreateAppointment(): bool;
}
