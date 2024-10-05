<?php

use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Models\Appointment;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

test('can confirm an appointment by the uuid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
        'invitee_type' => User::class,
    ]);

    $response = $this->post(route('appointments.confirm', $appointment->uuid))
        ->assertStatus(200);

    $appointment->refresh();
    expect($appointment->status)->toBe(AppointmentStatus::Confirmed);
});

test('can confirm an appointment by the uid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
        'invitee_type' => User::class,
    ]);

    $response = $this->post(route('appointments.confirm', '1-9'))
        ->assertStatus(200);

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Confirmed);
});

test('can`t confirm appointment for other users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => 999,
        'invitee_type' => User::class,
    ]);

    $this->post(route('appointments.confirm', '1-9'), [
        'title' => 'new title',
        'start_date' => now()->addDays(3)->format('Y-m-d'),
        'end_date' => now()->addDays(3)->addHour()->format('Y-m-d'),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ])
        ->assertStatus(404);
});

test('can confirm linked appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::factory()
        ->for($user)
        ->create();

    $appointment = $calendarAppointment->appointment;

    $this->post(route('appointments.confirm', $appointment->uuid))
        ->assertStatus(200);

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Confirmed);
});

test('can`t confirm finalized appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
        'invitee_type' => User::class,
        'status' => AppointmentStatus::Final,
    ]);

    $this->post(route('appointments.confirm', '1-9'))
        ->assertStatus(404);
});
