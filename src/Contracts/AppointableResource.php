<?php

namespace mindtwo\Appointable\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;

interface AppointableResource
{
    public function toAppointableResource(): JsonResource|array;
}
