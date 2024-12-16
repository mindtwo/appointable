<?php

use mindtwo\Appointable\Actions\CreateAppointment;
use mindtwo\Appointable\Helper\Ics;
use Workbench\App\Models\User;

beforeEach(function () {
    config()->set('appointable.default_organizer', 'test@example.com');
});

it('creates an ics file from appointment', function () {
    $data = [
        'title' => 'Test Appointment',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ];

    $appointment = app(CreateAppointment::class)($data, User::factory()->create());

    $ics = Ics::make($appointment);
    $content = $ics->toString();

    $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
    $this->assertStringContainsString('BEGIN:VEVENT', $content);
    $this->assertStringContainsString('END:VCALENDAR', $content);
    $this->assertStringContainsString('END:VEVENT', $content);

    $dtStart = $appointment->start->format('Ymd\THis\Z');
    $dtEnd = $appointment->end->format('Ymd\THis\Z');

    $this->assertStringContainsString("DTSTART:{$dtStart}", $content);
    $this->assertStringContainsString("DTEND:{$dtEnd}", $content);
});

it('updates the sequence on the appointment model', function () {
    $data = [
        'title' => 'Test Appointment',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ];

    $appointment = app(CreateAppointment::class)($data, User::factory()->create());

    $ics = Ics::make($appointment);
    $content = $ics->toString();

    $this->assertStringContainsString('SEQUENCE:0', $content);

    $this->travel(1)->days();
    $appointment->update([
        'title' => 'Updated Appointment',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDay(),
    ]);
    $appointment->refresh();

    $ics = Ics::make($appointment);
    $content = $ics->toString();

    $this->assertStringContainsString('SEQUENCE:1', $content);
});

it('can cancel an appointment', function () {
    $data = [
        'title' => 'Test Appointment',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ];

    $appointment = app(CreateAppointment::class)($data, User::factory()->create());

    $ics = Ics::make($appointment);
    $content = $ics->toString();

    $this->assertStringContainsString('METHOD:PUBLISH', $content);

    $this->travel(1)->days();

    $ics = Ics::make($appointment);
    $ics->cancel();
    $content = $ics->toString();

    $this->assertStringContainsString('METHOD:CANCEL', $content);
});
