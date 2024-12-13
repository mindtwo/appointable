<?php

namespace mindtwo\Appointable\Concerns;

use mindtwo\Appointable\Actions\CancelAppointment;
use mindtwo\Appointable\Actions\CreateLinkedAppointment;
use mindtwo\Appointable\Actions\UpdateLinkedAppointment;
use mindtwo\Appointable\Contracts\MaybeAutoCreated;
use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Scopes\LinkableDataScope;

trait BaseAppointable
{
    public static function bootBaseAppointable(): void
    {
        static::created(function ($model) {
            if ($model instanceof MaybeAutoCreated && ! $model->autoCreateAppointment()) {
                return;
            }

            $action = app(CreateLinkedAppointment::class);

            $action($model);
        });

        static::updated(function ($model) {
            $action = app(UpdateLinkedAppointment::class);

            $action($model);
        });

        static::deleting(function ($model) {
            $action = app(CancelAppointment::class);

            $model->loadMissing('appointment');
            if ($model->appointment) {
                $action($model->appointment, true);
            }
        });

        static::addGlobalScope(new LinkableDataScope);
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

        if (method_exists($this, 'getUid')) {
            return $this->getUid();
        }

        return $this->id;
    }

    /**
     * Get the sequence property of the model.
     */
    protected function getSequenceIncrementing(): int
    {
        if (! property_exists($this, 'sequence') && ! $this->hasAttribute('sequence')) {
            throw new \Exception('The sequence property is missing.');
        }

        $value = $this->sequence;

        // Increment the sequence / dispatch the increment action
        $this->callIncrementSequence();

        // Return the sequence (incremented by 1)
        return $value + 1;
    }

    /**
     * Get the sequence of the linked appointment.
     */
    protected function getLinkedSequence(): int
    {
        if (! $this->appointment()->exists()) {
            throw new \Exception('No appointment is linked to our model', 1);
        }

        $this->loadMissing('appointment');

        return $this->appointment->getSequence();
    }

    /**
     * Get the sequence of the appointment.
     * The sequence should be incremented every time the appointment is updated.
     */
    public function getSequence(): int
    {
        // if we have a sequence property, we can use it to increment the sequence
        if (property_exists($this, 'sequence') || $this->hasAttribute('sequence')) {
            return $this->getSequenceIncrementing();
        }

        // if we have an appointment, we can use the sequence of the appointment
        if ($this->appointment()->exists()) {
            return $this->getLinkedSequence();
        }

        // if we have a method to get the sequence, we can use it
        if (method_exists($this, 'getAppointmentSequence')) {
            return $this->getAppointmentSequence();
        }

        return 0;
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

        return '';
    }

    /**
     * Check if this appointable has a location.
     */
    public function hasLocation(): bool
    {
        return ! empty($this->getLocation());
    }

    /**
     * Get the base appointment status.
     */
    public function getBaseAppointmentStatus(): ?AppointmentStatus
    {
        if (property_exists($this, 'default_base_status') || property_exists($this, 'attributes') && array_key_exists('default_base_status', $this->attributes)) {
            return $this->default_base_status;
        }

        if (method_exists($this, 'getAppointmentStatus')) {
            return $this->getAppointmentStatus();
        }

        return null;
    }

    /**
     * Increment the sequence of the model.
     */
    protected function incrementSequence(): void
    {
        try {
            $this->increment('sequence');
        } catch (\Throwable $th) {
            throw new \Exception('The default implementation for incrementing the sequence is not working. Please override the incrementSequence method in your model.', 1, $th);
        }
    }

    /**
     * Call increment sequence.
     */
    private function callIncrementSequence(): void
    {
        $autoIncrement = true;

        // If the model has an auto_increment_sequence property, we can use it to determine if the sequence should be incremented.
        if (property_exists($this, 'auto_increment_sequence') || $this->hasAttribute('auto_increment_sequence')) {
            $autoIncrement = $this->auto_increment_sequence;
        }

        if (! $autoIncrement) {
            return;
        }

        // Increment the sequence anonymous job
        $dispatch = function () {
            // Increment the sequence
            $this->incrementSequence();
        };

        // If the code is running in the console, we can dispatch the job immediately.
        if (app()->runningInConsole()) {
            dispatch($dispatch);

            return;
        }

        // If the code is running in the web, we need to dispatch the job after the response.
        dispatch($dispatch)->afterResponse();
    }
}
