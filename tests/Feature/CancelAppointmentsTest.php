<?php

use Illuminate\Support\Str;
use mindtwo\Appointable\Models\Appointment;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

test('can delete an appointment by the uuid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $uuid = (string) Str::uuid()->toString();

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
    ]);

    $this->delete(route('appointments.cancel', $appointment->uuid))
        ->assertStatus(204);
});

test('can delete an appointment by the uid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
    ]);

    $this->delete(route('appointments.cancel', '1-9'))
        ->assertStatus(204);
});

test('can`t cancel appointment for other users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => 999,
    ]);

    $this->delete(route('appointments.cancel', '1-9'))
        ->assertStatus(404);
});

test('can`t cancel linked appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::factory()
        ->for($user)
        ->create();

    $appointment = $calendarAppointment->appointment;

    $this->delete(route('appointments.cancel', $appointment->uuid))
        ->assertStatus(403);
});
