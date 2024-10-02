<?php

namespace mindtwo\Appointable\Enums;

enum AppointmentStatus: string
{
    /**
     *  An invitation has been sent to the invitee.
     */
    case Invited = 'invited';

    /**
     * The appointment has been confirmed by the invitee.
     */
    case Confirmed = 'confirmed';

    /**
     * The appointment has been declined by the invitee.
     */
    case Declined = 'declined';

    /**
     * The appointment has a finite date and time. The invitee is not allowed to confirm or decline the appointment.
     */
    case Final = 'final';
}
