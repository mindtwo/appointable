<?php

use Illuminate\Support\Facades\Event;
use mindtwo\Appointable\Actions\CancelAppointment;
use mindtwo\Appointable\Actions\ConfirmAppointmentInvitation;
use mindtwo\Appointable\Actions\CreateAppointment;
use mindtwo\Appointable\Actions\DeclineAppointmentInvitation;
use mindtwo\Appointable\Actions\UpdateAppointment;
use mindtwo\Appointable\Events\AppointmentCanceled;
use mindtwo\Appointable\Events\AppointmentConfirmed;
use mindtwo\Appointable\Events\AppointmentCreated;
use mindtwo\Appointable\Events\AppointmentDeclined;
use mindtwo\Appointable\Events\AppointmentUpdated;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

it('should dispatch AppointmentCreated event', function () {
    Event::fake([
        AppointmentCreated::class,
    ]);

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
    ], User::factory()->create());

    Event::assertDispatched(AppointmentCreated::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentCreated event for linked model', function () {
    Event::fake([
        AppointmentCreated::class,
    ]);

    $user = User::factory()->create();

    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => $user->id,
    ]);

    $appointment = $calendarAppointment->appointment;

    Event::assertDispatched(AppointmentCreated::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentUpdated event', function () {
    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
    ], User::factory()->create());

    Event::fake([
        AppointmentUpdated::class,
    ]);

    app(UpdateAppointment::class)($appointment, [
        'title' => 'New Title',
        'is_entire_day' => true,
    ]);

    Event::assertDispatched(AppointmentUpdated::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentUpdated event with changes and original attributes', function () {
    Event::fake([
        AppointmentUpdated::class,
    ]);

    $start = now();
    $end = $start->copy()->addHour();

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => $start->format('Y-m-d'),
        'end_date' => $end->format('Y-m-d'),
        'start_time' => $start->format('H:i:s'),
        'end_time' => $end->format('H:i:s'),
    ], User::factory()->create());

    $orignalTitle = $appointment->title;

    $newStart = $start->copy()->addHour();
    $newEnd = $end->copy()->addHour();
    app(UpdateAppointment::class)($appointment, [
        'title' => 'New Title',
        'start_time' => $newStart->format('H:i:s'),
        'end_time' => $newEnd->format('H:i:s'),
    ]);

    Event::assertDispatched(AppointmentUpdated::class, function ($event) use ($appointment, $orignalTitle, $newStart, $newEnd, $start, $end) {
        return $event->appointment->is($appointment)
            && $event->changes['title'] === 'New Title'
            && $event->original['title'] === $orignalTitle
&& $event->changes['start_time'] === $newStart->format('H:i:s')
&& $event->original['start_time'] === $start->format('H:i:s')
            && $event->changes['end_time'] === $newEnd->format('H:i:s')
            && $event->original['end_time'] === $end->format('H:i:s')
            && in_array('title', $event->getDiffKeys())
            && in_array('start_time', $event->getDiffKeys())
            && in_array('end_time', $event->getDiffKeys());
    });
});

it('should dispatch AppointmentUpdated for linked event', function () {
    Event::fake([
        AppointmentUpdated::class,
    ]);

    $start = now();
    $user = User::factory()->create();
    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => $user->id,
        'start' => $start,
    ]);

    $appointment = $calendarAppointment->appointment;

    $orignalTitle = $appointment->title;

    $newStart = $start->copy()->addHour();
    $calendarAppointment->update([
        'title' => 'New Title',
        'start' => $newStart,
    ]);

    Event::assertDispatched(AppointmentUpdated::class, function ($event) use ($appointment, $orignalTitle, $newStart, $start) {
        return $event->appointment->is($appointment)
            && $event->changes['title'] === 'New Title'
            && $event->original['title'] === $orignalTitle
            && $event->changes['start_time'] === $newStart->format('H:i:s')
            && $event->original['start_time'] === $start->format('H:i:s')
            && in_array('title', $event->getDiffKeys())
            && in_array('start_date', $event->getDiffKeys());
    });
});

it('should dispatch AppointmentCanceled event', function () {
    Event::fake([
        AppointmentCanceled::class,
    ]);

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
    ], User::factory()->create());

    app(CancelAppointment::class)($appointment);

    Event::assertDispatched(AppointmentCanceled::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentCanceled event for deleted linked', function () {
    Event::fake([
        AppointmentCanceled::class,
    ]);
    $user = User::factory()->create();
    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => $user->id,
    ]);

    $appointment = $calendarAppointment->appointment;

    $calendarAppointment->delete();

    Event::assertDispatched(AppointmentCanceled::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentConfirmed event', function () {
    Event::fake([
        AppointmentConfirmed::class,
    ]);

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
        'status' => 'invited',
    ], User::factory()->create());

    app(ConfirmAppointmentInvitation::class)($appointment);

    Event::assertDispatched(AppointmentConfirmed::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentDeclined event', function () {
    Event::fake([
        AppointmentDeclined::class,
    ]);

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
        'status' => 'invited',
    ], User::factory()->create());

    app(DeclineAppointmentInvitation::class)($appointment);

    Event::assertDispatched(AppointmentDeclined::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});
