<?php

namespace mindtwo\Appointable\Concerns;

use mindtwo\Appointable\Actions\CancelAppointment;
use mindtwo\Appointable\Actions\CreateLinkedAppointment;
use mindtwo\Appointable\Actions\UpdateLinkedAppointment;

trait BaseAppointable
{
    public static function bootBaseAppointable(): void
    {
        static::created(function ($model) {
            $action = app(CreateLinkedAppointment::class);

            $action($model);
        });

        static::updated(function ($model) {
            $action = app(UpdateLinkedAppointment::class);

            $action($model);
        });

        static::deleting(function ($model) {
            $action = app(CancelAppointment::class);

            $action($model->appointment);
        });
    }

    /**
     * Get the linked appointment.
     */
    public function appointment(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\mindtwo\Appointable\Models\Appointment::class, 'linkable');
    }

    /**
     * Get a unique identifier for the appointment.
     */
    public function getAppointmentUid(): string
    {
        if (property_exists($this, 'uid')) {
            return $this->uid;
        }

        $id = $this->id ?? 0;
        if (property_exists($this, 'uuid')) {
            return $this->uuid;
        }

        return $this->id;
    }

    /**
     * Get the sequence of the appointment.
     * The sequence should be incremented every time the appointment is updated.
     */
    public function getSequence(): int
    {
        return $this->sequence ?? 0;
    }

    /**
     * Get the title of the appointment.
     */
    public function getAppointmentTitle(): ?string
    {
        if (property_exists($this, 'title') || property_exists($this, 'attributes') && array_key_exists('title', $this->attributes)) {
            return $this->title;
        }

        if (method_exists($this, 'getTitle')) {
            return $this->getTitle();
        }

        return null;
    }

    /**
     * Get the description of the appointment.
     */
    public function getAppointmentDescription(): ?string
    {
        if (property_exists($this, 'description') || property_exists($this, 'attributes') && array_key_exists('description', $this->attributes)) {
            return $this->description;
        }

        if (method_exists($this, 'getDescription')) {
            return $this->getDescription();
        }

        return null;
    }

    /**
     * Get the location of this appointable.
     */
    public function getLocation(): string
    {
        if (property_exists($this, 'location') || property_exists($this, 'attributes') && array_key_exists('location', $this->attributes)) {
            return $this->location;
        }

        if (method_exists($this, 'getLocation')) {
            return $this->getLocation();
        }

        return '';
    }

    /**
     * Check if this appointable has a location.
     */
    public function hasLocation(): bool
    {
        return ! empty($this->getLocation());
    }
}
