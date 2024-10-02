<?php

use Illuminate\Support\Str;
use mindtwo\Appointable\Models\Appointment;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

test('can update an appointment by the uuid', function () {
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

    $newStart = now()->addDays(3);
    $newEnd = now()->addDays(3)->addHour();
    $data = [
        'title' => 'new title',
        'start_date' => $newStart->format('Y-m-d'),
        'end_date' => $newEnd->format('Y-m-d'),
        'start_time' => $newStart->format('H:i:s'),
        'end_time' => $newEnd->format('H:i:s'),
    ];

    $response = $this->put(route('appointments.update', $appointment->uuid), $data)
        ->assertStatus(200);

    // Check if the appointment was updated
    $this->assertDatabaseHas('appointments', [
        'title' => 'new title',
        'start_time' => $newStart->copy()->format('H:i:s'),
        'end_time' => $newEnd->copy()->format('H:i:s'),
        'start_date' => $newStart->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
        'end_date' => $newEnd->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
    ]);

    $response->assertJsonFragment([
        'title' => 'new title',
        'start_date' => $newStart->copy()->format('Y-m-d'),
        'end_date' => $newEnd->copy()->format('Y-m-d'),
        'start_time' => $newStart->copy()->format('H:i:s'),
        'end_time' => $newEnd->copy()->format('H:i:s'),
    ]);
});

test('can update an appointment by the uid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => $user->id,
    ]);

    $newStart = now()->addDays(3);
    $newEnd = now()->addDays(3)->addHour();
    $data = [
        'title' => 'new title',
        'start_date' => $newStart->format('Y-m-d'),
        'end_date' => $newEnd->format('Y-m-d'),
        'start_time' => $newStart->format('H:i:s'),
        'end_time' => $newEnd->format('H:i:s'),
    ];

    $response = $this->put(route('appointments.update', '1-9'), $data)
        ->assertStatus(200);

    // Check if the appointment was updated
    // Check if the appointment was updated
    $this->assertDatabaseHas('appointments', [
        'title' => 'new title',
        'start_time' => $newStart->copy()->format('H:i:s'),
        'end_time' => $newEnd->copy()->format('H:i:s'),
        'start_date' => $newStart->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
        'end_date' => $newEnd->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
    ]);

    $response->assertJsonFragment([
        'title' => 'new title',
        'start_date' => $newStart->copy()->format('Y-m-d'),
        'end_date' => $newEnd->copy()->format('Y-m-d'),
        'start_time' => $newStart->copy()->format('H:i:s'),
        'end_time' => $newEnd->copy()->format('H:i:s'),
    ]);
});

test('can change from specified times to entire day', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'invitee_id' => $user->id,
    ]);

    $newStart = now()->addDays(3);
    $newEnd = now()->addDays(3)->addHour();
    $data = [
        'is_entire_day' => true,
        'title' => 'new title',
        'start_date' => $newStart->format('Y-m-d'),
        'end_date' => $newEnd->format('Y-m-d'),
    ];

    $response = $this->put(route('appointments.update', '1-9'), $data)
        ->assertStatus(200);

    // Check if the appointment was updated
    $this->assertDatabaseHas('appointments', [
        'title' => 'new title',
        'start_date' => $newStart->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
        'end_date' => $newEnd->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
        'start_time' => null,
        'end_time' => null,
    ]);

    $response->assertJsonFragment([
        'title' => 'new title',
        'start_date' => $newStart->copy()->format('Y-m-d'),
        'end_date' => $newEnd->copy()->format('Y-m-d'),
        'start_time' => null,
        'end_time' => null,
        'is_entire_day' => true,
    ]);
});

test('can`t update appointment for other users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $appointment = Appointment::create([
        'uid' => '1-9',
        'title' => 'Test Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay()->addHour(),
        'invitee_id' => 999,
    ]);

    $this->put(route('appointments.update', '1-9'), [
        'title' => 'new title',
        'start_date' => now()->addDays(3)->format('Y-m-d'),
        'end_date' => now()->addDays(3)->addHour()->format('Y-m-d'),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ])
        ->assertStatus(404);
});

test('can`t update linked appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $calendarAppointment = CalendarAppointment::factory()
        ->for($user)
        ->create();

    $appointment = $calendarAppointment->appointment;

    $this->put(route('appointments.update', $appointment->uuid), [
        'title' => 'new title',
        'start_date' => now()->addDays(3)->format('Y-m-d'),
        'end_date' => now()->addDays(3)->addHour()->format('Y-m-d'),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ])
        ->assertStatus(403);
});
