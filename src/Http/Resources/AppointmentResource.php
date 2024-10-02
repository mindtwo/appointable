<?php

namespace mindtwo\Appointable\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AppointmentResource
 *
 * @mixin \mindtwo\Appointable\Models\Appointment
 */
class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'uid' => $this->uid,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'is_entire_day' => $this->is_entire_day,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
        ];
    }
}
