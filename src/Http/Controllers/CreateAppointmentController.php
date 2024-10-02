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

        $inviteeId = $data['invitee'] ?? $request->user()?->id;

        if (! $inviteeId) {
            return response()->json(['error' => 'Invitee not specified'], 422);
        }

        $appointment = app(CreateAppointment::class)($data, $inviteeId, $request->boolean('is_entire_day', false));

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
