<?php

use Illuminate\Support\Str;
use mindtwo\Appointable\Actions\CreateLinkedAppointment;
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
        'invitee_type' => User::class,
        'status' => 'invited',
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
        'invitee_type' => User::class,
        'status' => 'invited',
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
        'invitee_type' => User::class,
        'status' => 'invited',
    ]);

    $this->post(route('appointments.decline', '1-9'))
        ->assertStatus(404);
});

test('can`t decline linked appointments per default', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::factory()
        ->for($user)
        ->create();

    $appointment = $calendarAppointment->appointment;

    $this->post(route('appointments.decline', $appointment->uuid))
        ->assertStatus(400);
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
        'invitee_type' => User::class,
        'status' => AppointmentStatus::Final,
    ]);

    $this->post(route('appointments.decline', '1-9'))
        ->assertStatus(404);
});

test('can decline linked appointments if a default_status is set', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::withoutEvents(fn () => CalendarAppointment::factory()
        ->for($user)
        ->create());

    $calendarAppointment->default_base_status = AppointmentStatus::Invited;

    // Create the linked appointment
    app(CreateLinkedAppointment::class)($calendarAppointment);

    $appointment = $calendarAppointment->appointment;

    $this->post(route('appointments.decline', $appointment->uuid))
        ->assertStatus(200);
});
