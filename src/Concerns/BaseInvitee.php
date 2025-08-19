<?php

namespace mindtwo\Appointable\Concerns;

use Illuminate\Database\Eloquent\Model;

// @phpstan-ignore-next-line - This trait is used by models that implement the Invitee contract
trait BaseInvitee
{
    /**
     * Get the invitee model.
     *
     * @return Model|null
     */
    public function getInvitee(): ?Model
    {

        if ($this instanceof Model) {
            return $this;
        }

        return null;

    }

    /**
     * Get the ID of the invitee.
     *
     * @return int - The ID of the invitee.
     */
    public function getInviteeId(): int
    {
        if ($this instanceof Model) {
            return $this->getKey();
        }

        if (method_exists($this, 'getKey')) {
            return $this->getKey();
        }

        throw new \RuntimeException('Invitee does not have a valid ID.');
    }

    /**
     * Get the class name of the invitee model.
     *
     * @return class-string<Model> - The class name of the invitee model.
     */
    public function getInviteeType(): string
    {
        // If the model has a morph class, return it
        if (method_exists($this, 'getMorphClass')) {
            return $this->getMorphClass();
        }

        if ($this instanceof Model) {
            return get_class($this);
        }

        throw new \RuntimeException('Invitee does not have a valid model class.');
    }
}
