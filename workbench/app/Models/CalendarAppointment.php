<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use mindtwo\Appointable\Concerns\BaseAppointable;
use mindtwo\Appointable\Contracts\BaseAppointable as AppointableContract;

/**
 * @property string $title
 * @property string $description
 * @property \DateTime $start
 * @property \DateTime $end
 */
class CalendarAppointment extends Model implements AppointableContract
{
    use BaseAppointable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getInvitee(): ?Model
    {
        return $this->user;
    }

    /**
     * Get the start date with time of the appointment.
     */
    public function getAppointmentStart(): Carbon
    {
        return $this->start;
    }

    /**
     * Get the end date with time of the appointment.
     */
    public function getAppointmentEnd(): ?Carbon
    {
        return $this->end;
    }
}
