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

it('should dispatch AppointmentCreated event', function () {
    Event::fake([
        AppointmentCreated::class,
    ]);

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
    ], 1);

    Event::assertDispatched(AppointmentCreated::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentCreated event for linked model', function () {
    Event::fake([
        AppointmentCreated::class,
    ]);

    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => 1,
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
    ], 1);

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

    $appointment = app(CreateAppointment::class)([
        'title' => 'Test Appointment',
        'start_date' => now(),
        'end_date' => now()->addHour(),
    ], 1);

    $orignalTitle = $appointment->title;

    app(UpdateAppointment::class)($appointment, [
        'title' => 'New Title',
        'is_entire_day' => true,
    ]);

    Event::assertDispatched(AppointmentUpdated::class, function ($event) use ($appointment, $orignalTitle) {
        return $event->appointment->is($appointment)
            && $event->changes['title'] === 'New Title'
            && $event->original['title'] === $orignalTitle;
    });
});

it('should dispatch AppointmentUpdated for linked event', function () {
    Event::fake([
        AppointmentUpdated::class,
    ]);

    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => 1,
    ]);

    $appointment = $calendarAppointment->appointment;

    $orignalTitle = $appointment->title;

    $calendarAppointment->update([
        'title' => 'New Title',
    ]);

    Event::assertDispatched(AppointmentUpdated::class, function ($event) use ($appointment, $orignalTitle) {
        // TODO check if changes are correct

        return $event->appointment->is($appointment)
            && $event->changes['title'] === 'New Title'
            && $event->original['title'] === $orignalTitle;
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
    ], 1);

    app(CancelAppointment::class)($appointment);

    Event::assertDispatched(AppointmentCanceled::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});

it('should dispatch AppointmentCanceled event for deleted linked', function () {
    Event::fake([
        AppointmentCanceled::class,
    ]);

    $calendarAppointment = CalendarAppointment::factory()->create([
        'user_id' => 1,
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
    ], 1);

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
    ], 1);

    app(DeclineAppointmentInvitation::class)($appointment);

    Event::assertDispatched(AppointmentDeclined::class, function ($event) use ($appointment) {
        return $event->appointment->is($appointment);
    });
});
