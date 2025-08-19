<?php

namespace mindtwo\Appointable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use mindtwo\Appointable\Actions\DeclineAppointmentInvitation;
use mindtwo\Appointable\Contracts\Invitee;
use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Models\Appointment;

class DeclineInvitationController
{
    /**
     * Decline an appointment.
     */
    public function __invoke(Request $request, string $uuidOrUid, DeclineAppointmentInvitation $declineAppointmentInvitation): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user || ! $user instanceof Invitee, 401);

        $appointment = Appointment::query()
            // find appointment by uuid or uid
            ->where(fn ($q) => $q->where('uuid', $uuidOrUid)->orWhere('uid', $uuidOrUid))
            // status is not final
            ->where(fn ($q) => $q->whereNull('status')->orWhereNot('status', AppointmentStatus::Final->value))
            ->where([
                'invitee_id' => $user->getInviteeId(),
                'invitee_type' => $user->getInviteeType(),
            ])
            ->with('linkable')
            ->first();

        abort_if(! $appointment, 404);

        $result = $declineAppointmentInvitation($appointment);

        return $result ? response()->json([
            'message' => 'Appointment declined',
        ], 200) : response()->json([
            'message' => 'Appointment could not be declined',
        ], 400);
    }
}
