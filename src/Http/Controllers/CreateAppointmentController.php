<?php

namespace mindtwo\Appointable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use mindtwo\Appointable\Actions\CreateAppointment;
use mindtwo\Appointable\Http\Requests\CreateAppointmentRequest;

class CreateAppointmentController
{
    /**
     * Create a new appointment.
     */
    public function __invoke(CreateAppointmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invitee = $data['invitee'] ?? $request->user();

        if (is_int($invitee)) {
            $defaultInviteeClass = config('appointable.models.user');

            if (! is_a($defaultInviteeClass, \Illuminate\Database\Eloquent\Model::class, true)) {
                return response()->json(['error' => 'Invalid default invitee class'], 500);
            }

            $invitee = $defaultInviteeClass::find($invitee);
        }

        if (! $invitee) {
            return response()->json(['error' => 'Invitee not specified'], 422);
        }

        $appointment = app(CreateAppointment::class)($data, $invitee, $request->boolean('is_entire_day', false));

        $appointmentResourceClass = config('appointable.resources.appointment', \mindtwo\Appointable\Http\Resources\AppointmentResource::class);

        // Check if the appointment resource class is valid
        if (! is_a($appointmentResourceClass, \mindtwo\Appointable\Http\Resources\AppointmentResource::class, true)) {
            return response()->json(['error' => 'Invalid appointment resource class'], 500);
        }

        return response()->json([
            'data' => $appointmentResourceClass::make($appointment),
        ], 201);
    }
}
