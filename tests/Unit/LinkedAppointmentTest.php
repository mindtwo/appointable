<?php

use Illuminate\Support\Facades\Queue;
use mindtwo\Appointable\Models\Appointment;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

it('creates a linked appointment if appointable is created', function () {
    $user = User::factory()->create();

    // Given we have an appointable
    $appointable = CalendarAppointment::factory()->for($user)->create();
    $appointable->refresh();

    // Then the appointment should be linked to the appointable
    expect($appointable->appointment)->toBeTruthy();

    $appointment = $appointable->appointment;

    expect($appointment->invitee_id)->toBe($user->id)
        ->and($appointment->start_date->format('d-m-Y'))->toBe($appointable->start->format('d-m-Y'))
        ->and($appointment->end_date->format('d-m-Y'))->toBe($appointable->end->format('d-m-Y'))
        ->and($appointment->start_time)->toBe($appointable->start->format('H:i:s'))
        ->and($appointment->end_time)->toBe($appointable->end->format('H:i:s'));
});

it('updates a linked appointment if the appointable is updated', function () {

    $user = User::factory()->create();

    // Given we have an appointable
    $appointable = CalendarAppointment::factory()->for($user)->create();

    $appointable->refresh();

    // When the appointable is updated
    $appointable->update([
        'start' => now()->addDay(),
        'end' => now()->addDay()->addHour(),
    ]);

    $appointable->refresh();

    // Then the appointment should be updated as well
    $appointment = $appointable->appointment;

    expect($appointment->invitee_id)->toBe($user->id)
        ->and($appointment->start_date->format('d-m-Y'))->toBe($appointable->start->format('d-m-Y'))
        ->and($appointment->end_date->format('d-m-Y'))->toBe($appointable->end->format('d-m-Y'))
        ->and($appointment->start_time)->toBe($appointable->start->format('H:i:s'))
        ->and($appointment->end_time)->toBe($appointable->end->format('H:i:s'));

});

it('deletes the linked appointment if the appointable is deleted', function () {
    $user = User::factory()->create();

    // Given we have an appointable
    $appointable = CalendarAppointment::factory()->for($user)->create();

    $appointable->refresh();

    // Then the appointable should have an appointment
    $appointment = $appointable->appointment;
    expect($appointment)->not->toBeNull();

    // When the appointable is deleted
    $appointable->delete();

    $appointable = CalendarAppointment::find($appointable->id);

    $appointment = Appointment::find($appointment->id);

    // Then the appointment should be deleted as well
    expect($appointable)->toBeNull()
        ->and($appointment)->toBeNull();
});

it('gets the sequence of the linked appointment via appointment', function () {
    Queue::fake();

    $user = User::factory()->create();

    // Given we have an appointable
    $appointable = CalendarAppointment::factory()->for($user)->create();
    $appointable->fresh();

    // Then the sequence should be 0
    expect($appointable->getSequence())->toBe(0);
    $appointable->fresh();

    // When the appointable is updated
    $appointable->update([
        'start' => now()->addDay(),
        'end' => now()->addDay()->addHour(),
    ]);

    $appointable->refresh();

    // Then the sequence should be 1
    expect($appointable->getSequence())->toBe(1);
});
