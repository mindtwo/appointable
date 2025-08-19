<?php

namespace mindtwo\Appointable\Contracts;

interface Invitee
{

    /**
     * Get the invitee model.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getInvitee(): ?\Illuminate\Database\Eloquent\Model;

    /**
     * Get the ID of the invitee.
     *
     * @return int - The ID of the invitee.
     */
    public function getInviteeId(): int;

    /**
     * Get the class name of the invitee model.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model> - The class name of the invitee model.
     */
    public function getInviteeType(): string;

}
