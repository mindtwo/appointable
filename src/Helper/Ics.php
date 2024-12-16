<?php

namespace mindtwo\Appointable\Helper;

use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Str;
use mindtwo\Appointable\Contracts\BaseAppointable as AppointableContract;
use mindtwo\Appointable\Contracts\LocatableAppointment;
use mindtwo\Appointable\Contracts\MaybeIsEntireDay;
use Stringable;

class Ics implements Stringable
{
    private AppointableContract $appointment;

    private IcsFile $icsFile;

    private function __construct(AppointableContract $appointment, ?string $organizer = null, ?string $attendee = null)
    {
        $this->appointment = $appointment;

        $organizer = $organizer ?? config('appointable.default_organizer');

        if (! $organizer) {
            throw new \InvalidArgumentException('No organizer set. Please set an organizer in the config or pass it as second argument.');
        }

        $this->icsFile = new IcsFile(
            $appointment->getAppointmentUid(),
            $organizer,
            $attendee,
        );

        $this->icsFile
            ->sequence($appointment->getSequence())
            ->start($appointment->getAppointmentStart())
            ->when(
                $appointment->getAppointmentEnd(),
                fn ($ics) => $ics->end($appointment->getAppointmentEnd())
            );

        // Set appointment title and description if available
        if ($title = $appointment->getAppointmentTitle()) {
            $this->icsFile->summary($title);
        }

        if ($description = $appointment->getAppointmentDescription()) {
            $this->icsFile->description($description);
        }

        // Check if the appointment is an entire day appointment
        if ($appointment instanceof MaybeIsEntireDay && $appointment->isEntireDay()) {
            $this->icsFile->entireDay();
        }

        // Check if the appointment has a location
        if ($appointment instanceof LocatableAppointment && $appointment->hasLocation()) {
            $this->icsFile->location($appointment->getLocation());
        }
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->icsFile->toString();
    }

    public function filename(): string
    {
        $title = $this->appointment->getAppointmentTitle() ?? 'appointment';

        return Str::slug($title, '-').'.ics';
    }

    public function cancel(): self
    {
        $this->icsFile->cancel();

        return $this;
    }

    /**
     * Get as mail attchment.
     */
    public function mailAttachment(): Attachment
    {
        return Attachment::fromData(fn () => $this->toString(), $this->filename())
            ->withMime('text/calendar');
    }

    /**
     * Download the ICS file.
     */
    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->toString();
        }, $this->filename());
    }

    public static function make(AppointableContract $appointment, ?string $organizer = null, ?string $attendee = null): self
    {
        return new self($appointment, $organizer, $attendee);
    }
}
