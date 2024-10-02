<?php

namespace mindtwo\Appointable\Contracts;

interface LocatableAppointment
{
    /**
     * Get the location of this appointable.
     */
    public function getLocation(): string;

    /**
     * Check if this appointable has a location.
     */
    public function hasLocation(): bool;
}
