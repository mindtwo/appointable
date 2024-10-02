<?php

namespace mindtwo\Appointable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use mindtwo\Appointable\Actions\UpdateAppointment;
use mindtwo\Appointable\Http\Requests\UpdateAppointmentRequest;
use mindtwo\Appointable\Models\Appointment;

class UpdateAppointmentController
{
    /**
     * Update an appointment.
     */
    public function __invoke(UpdateAppointmentRequest $request, string $uuidOrUid, UpdateAppointment $updateAppointment): JsonResponse
    {
        abort_if(! $request->user(), 401);

        $appointment = Appointment::query()
            ->where(fn ($q) => $q->where('uuid', $uuidOrUid)->orWhere('uid', $uuidOrUid))
            ->where('invitee_id', $request->user()->id)
            ->with('linkable')
            ->first();

        abort_if(! $appointment, 404);
        abort_if($appointment->linkable != null, 403, 'Cannot cancel linked appointments manually');

        $appointment = $updateAppointment($appointment, $request->validated());

        $appointmentResourceClass = config('appointable.resources.appointment', \mindtwo\Appointable\Http\Resources\AppointmentResource::class);

        // Check if the appointment resource class is valid
        if (! is_a($appointmentResourceClass, \mindtwo\Appointable\Http\Resources\AppointmentResource::class, true)) {
            return response()->json(['error' => 'Invalid appointment resource class'], 500);
        }

        return response()->json([
            'data' => $appointmentResourceClass::make($appointment),
        ], 200);
    }
}
