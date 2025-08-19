<?php

namespace mindtwo\Appointable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use mindtwo\Appointable\Actions\CancelAppointment;
use mindtwo\Appointable\Contracts\Invitee;
use mindtwo\Appointable\Models\Appointment;

class CancelAppointmentController
{
    /**
     * Cancel/Delete an appointment.
     */
    public function __invoke(Request $request, string $uuidOrUid): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user || ! $user instanceof Invitee, 401);

        $appointment = Appointment::query()
            ->where(fn ($q) => $q->where('uuid', $uuidOrUid)->orWhere('uid', $uuidOrUid))
            ->where([
                'invitee_id' => $user->getInviteeId(),
                'invitee_type' => $user->getInviteeType(),
            ])
            ->with('linkable')
            ->first();

        abort_if(! $appointment, 404);
        abort_if($appointment->linkable != null, 403, 'Cannot cancel linked appointments manually');

        app(CancelAppointment::class)($appointment);

        return response()->json([], 204);
    }
}
