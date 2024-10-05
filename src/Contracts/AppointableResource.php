<?php

namespace mindtwo\Appointable\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;

interface AppointableResource
{
    /**
     * Convert the resource to an appointable resource.
     *
     * @return JsonResource|array<string, mixed>
     */
    public function toAppointableResource(): JsonResource|array;
}
