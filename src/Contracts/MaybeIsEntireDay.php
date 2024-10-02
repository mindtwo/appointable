<?php

interface MaybeIsEntireDay
{
    /**
     * Check if this appointable takes the entire day.
     */
    public function isEntireDay(): bool;
}
