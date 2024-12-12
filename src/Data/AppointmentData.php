<?php

namespace mindtwo\Appointable\Data;

use Illuminate\Contracts\Support\Arrayable;
use mindtwo\Appointable\Contracts\AppointableResource;
use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Models\Appointment;

class AppointmentData
{
    public function __construct(
        public string $uid,
        public string $uuid,
        public ?string $title,
        public ?string $description,
        public ?string $location,
        public bool $is_entire_day,
        public string $start_date,
        public ?string $end_date,
        public ?string $start_time,
        public ?string $end_time,
        public ?AppointmentStatus $status,

        /** @var null|Arrayable<string,mixed>|AppointableResource */
        public null|Arrayable|AppointableResource $linkable,

        public ?float $gridrow = null,
        public ?float $span = null,
        public ?int $same = null,
    ) {}

    /**
     * Convert the object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $linkData = $this->linkable;

        if ($linkData instanceof AppointableResource) {
            $linkData = $linkData->toAppointableResource();
        }

        if ($linkData instanceof Arrayable) {
            $linkData = $linkData->toArray();
        }

        return [
            'uid' => $this->uid,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'is_entire_day' => $this->is_entire_day,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status?->value,
            'linkable' => $linkData,
            'gridrow' => $this->gridrow,
            'span' => $this->span,
            'same' => $this->same,
        ];
    }

    public static function fromAppointment(Appointment $appointment): self
    {
        $linkable = $appointment->relationLoaded('linkable')
            ? $appointment->linkable : null;

        return new self(
            $appointment->uid,
            $appointment->uuid,
            $appointment->title,
            $appointment->description,
            $appointment->location,
            $appointment->is_entire_day,
            $appointment->start_date->format('Y-m-d'),
            $appointment->end_date->format('Y-m-d'),
            $appointment->start_time,
            $appointment->end_time,
            $appointment->status,
            $linkable,
        );
    }
}
