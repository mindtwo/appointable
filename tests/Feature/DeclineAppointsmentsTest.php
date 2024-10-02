<?php

use Illuminate\Support\Str;
use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Models\Appointment;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

test('can decline an appointment by the uuid', function () {
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

    $response = $this->post(route('appointments.decline', $appointment->uuid))
        ->assertStatus(200);

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Declined);
});

test('can decline an appointment by the uid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
    ]);

    $response = $this->post(route('appointments.decline', '1-9'))
        ->assertStatus(200);

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Declined);
});

test('can`t decline appointment for other users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => 999,
    ]);

    $this->post(route('appointments.decline', '1-9'))
        ->assertStatus(404);
});

test('can decline linked appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::factory()
        ->for($user)
        ->create();

    $appointment = $calendarAppointment->appointment;

    $this->post(route('appointments.decline', $appointment->uuid))
        ->assertStatus(200);
});

test('can`t decline finalized appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
        'status' => AppointmentStatus::Final,
    ]);

    $this->post(route('appointments.decline', '1-9'))
        ->assertStatus(404);
});
