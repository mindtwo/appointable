<?php

namespace mindtwo\Appointable\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use mindtwo\Appointable\Casts\TimeCast;
use mindtwo\Appointable\Contracts\BaseAppointable as AppointableContract;
use mindtwo\Appointable\Enums\AppointmentStatus;
use mindtwo\Appointable\Helper\Ics;
use mindtwo\Appointable\Helper\TimesAndDates;
use mindtwo\LaravelAutoCreateUuid\AutoCreateUuid;

/**
 * Ids and relations
 *
 * @property int $id
 * @property string $uuid
 * @property string $uid
 * @property int $sequence
 * @property ?int $invitee_id
 * @property ?int $linkable_id
 * @property ?string $linkable_type
 * @property bool $is_entire_day
 * @property ?AppointmentStatus $status
 * @property Carbon $start_date
 * @property ?Carbon $end_date
 * @property ?Carbon $start_time
 * @property ?Carbon $end_time
 * @property-read string $start_time_local
 * @property-read string $end_time_local
 * @property-read Carbon $start
 * @property-read Carbon $end
 *
 * Properties to better describe the appointment
 * @property ?string $title
 * @property ?string $description
 * @property ?string $location
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * TODO: timezone handling
 */
class Appointment extends Model implements AppointableContract
{
    use AutoCreateUuid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id', 'uuid'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => TimeCast::class.':second',
        'end_time' => TimeCast::class.':second',
        'status' => AppointmentStatus::class,
        'is_entire_day' => 'boolean',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'start_time_local',
        'end_time_local',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        // Generate a unique identifier for the appointment
        static::creating(function (Appointment $appointment) {
            if (! isset($appointment->uid)) {
                $appointment->uid = $appointment->generateUid();
            }
        });
    }

    /**
     * Get the linkable model
     *
     * @phpstan-ignore-next-line
     */
    public function linkable(): MorphTo
    {
        return $this
            ->morphTo(__FUNCTION__, 'linkable_type', 'linkable_id');
    }

    /**
     * Get the invitee model
     *
     * @phpstan-ignore-next-line
     */
    public function invitee(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the start time in the user's timezone
     *
     * @return Attribute<string, null>
     */
    protected function startTimeLocal(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->start_time) ? TimesAndDates::formatTimeToUserTimezone($this->start_time) : null,
        );
    }

    /**
     * Get the end time in the user's timezone
     *
     * @return Attribute<string, null>
     */
    protected function endTimeLocal(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->end_time) ? TimesAndDates::formatTimeToUserTimezone($this->end_time) : null,
        );
    }

    /**
     * Get the start date and time as Carbon instance
     *
     * @return Attribute<Carbon, null>
     */
    protected function start(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date->setTimeFromTimeString($this->start_time),
        );
    }

    /**
     * Get the end date and time as Carbon instance
     *
     * @return Attribute<Carbon, null>
     */
    protected function end(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_date->setTimeFromTimeString($this->end_time),
        );
    }

    /**
     * Generate a unique identifier for the appointment
     */
    public function generateUid(): string
    {
        $uuid = Str::uuid()->toString();

        return md5($uuid.$this->start_date.$this->start_time);
    }

    /**
     * Create an ICS file for the appointment
     *
     * @return Ics
     */
    public function createIcs()
    {
        $ics = Ics::make($this);

        if ($this->status === AppointmentStatus::Declined) {
            $ics->cancel();
        }

        return $ics;
    }

    /**
     * Get the ICS file as attachment
     *
     * @return Attachment
     */
    public function attachment()
    {
        return $this->createIcs()->mailAttachment();
    }

    /**
     * Download the appointment as ICS file
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadIcs()
    {
        return $this->createIcs()->download();
    }

    /**
     * Get the appointment data as array
     *
     * @return array<string, mixed>
     */
    public function toAppointmentData(): array
    {
        return collect([
            'uid' => $this->uid,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'is_entire_day' => $this->is_entire_day,
            'start_date' => $this->start_date->setTime(0, 0, 0),
            'end_date' => $this->end_date->setTime(0, 0, 0),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ])->filter()->toArray();
    }

    /**
     * Get a unique identifier for the appointment.
     */
    public function getAppointmentUid(): string
    {
        return $this->uid;
    }

    /**
     * Get the sequence of the appointment.
     * The sequence should be incremented every time the appointment is updated.
     */
    public function getSequence(): int
    {
        $value = $this->sequence;

        // Increment the sequence anonymous job
        $dispatch = function () {
            $this->increment('sequence');
        };

        // If the code is running in the console, we can dispatch the job immediately.
        if (app()->runningInConsole()) {
            dispatch($dispatch);
        } else {
            // If the code is running in the web, we need to dispatch the job after the response.
            dispatch($dispatch)->afterResponse();
        }

        // Return the sequence (incremented by 1)
        return $value + 1;
    }

    /**
     * Get the id of the invitee.
     */
    public function getInvitee(): ?Model
    {
        return $this->invitee;
    }

    /**
     * Get the title of the appointment.
     */
    public function getAppointmentTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the description of the appointment.
     */
    public function getAppointmentDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the start date with time of the appointment.
     */
    public function getAppointmentStart(): Carbon
    {
        return $this->start->copy();
    }

    /**
     * Get the end date with time of the appointment.
     */
    public function getAppointmentEnd(): ?Carbon
    {
        return $this->end->copy();
    }
}
