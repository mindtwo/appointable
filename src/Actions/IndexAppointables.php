<?php

namespace mindtwo\Appointable\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use mindtwo\Appointable\Data\AppointmentData;
use mindtwo\Appointable\Data\CalendarMeta;
use mindtwo\Appointable\Enums\CalendarInterval;
use mindtwo\Appointable\Models\Appointment;

class IndexAppointables
{
    private Carbon $current_date;

    private CalendarInterval $calendarInterval;

    /**
     * Handle the action.
     *
     * @return array<mixed>
     */
    public function __invoke(Carbon $date, CalendarInterval $calendarInterval): array
    {
        abort_if(! Auth::check(), 401);

        $this->current_date = $date;
        $this->calendarInterval = $calendarInterval;

        $currentDay = $date->day;
        $currentWeekNumber = $date->week;
        $currentMonth = $date->month;
        $currentYear = $date->year;

        // get meta information
        $meta = $this->getMetaInformation($calendarInterval);

        // get the calendar grid
        $days = $this->getCalendarGrid($meta->start, $meta->end);

        // Get appointments
        $appointments = $this->getAppointments($meta->start, $meta->end);

        $groupedAppointments = $appointments->reduce(function ($carry, Appointment $appointment) use ($appointments) {
            $dateKey = $appointment->start_date->format('Y-m-d');

            $start = Carbon::parse($appointment->start_date->format('Y-m-d').' '.$appointment->start_time)->floorMinutes(5);
            $start_of_day = Carbon::parse($appointment->start_date);
            $end = Carbon::parse($appointment->start_date->format('Y-m-d').' '.$appointment->end_time)->ceilMinutes(5);

            $appointmentData = AppointmentData::fromAppointment($appointment);

            // calculate grid row and span
            $appointmentData->gridrow = ($start->diffInMinutes($start_of_day) / 5) + 2;
            $appointmentData->span = ($end->diffInMinutes($start) / 5);
            if ($appointmentData->span < 6) {
                $appointmentData->span = 6;
            }

            $appointmentData->same = -1;

            foreach ($appointments as $inner_appointment) {
                $start_inner = Carbon::parse($inner_appointment->start_date->format('Y-m-d').' '.$inner_appointment->start_time)->floorMinutes(5);
                $end_inner = Carbon::parse($inner_appointment->start_date->format('Y-m-d').' '.$inner_appointment->end_time)->ceilMinutes(5);
                if ($start->isBetween($start_inner, $end_inner) || $end->isBetween($start_inner, $end_inner)) {
                    $appointmentData->same += 1;
                }
            }

            $carry[$dateKey][] = $appointmentData->toArray();

            return $carry;
        }, []);

        return [
            'data' => [
                'current_date' => $date,
                'calendar_interval' => $calendarInterval->value,
                // current date information
                'current_day' => $currentDay,
                'current_week' => $currentWeekNumber,
                'current_month' => $currentMonth,
                'current_year' => $currentYear,
                'current_month_name' => trans('calendar.months.'.$currentMonth),
                // Days of the month
                'days' => $days,
                // appointment data
                'appointments' => $groupedAppointments,
                // month names
                'month_names' => [],
                // week days
                'week_days' => [],
                // short week days
                'short_days' => trans('calendar.short_days'),
            ],
            'meta' => $meta->toArray(),
        ];
    }

    /**
     * Get appointments.
     *
     * @return Collection<int, Appointment>
     */
    protected function getAppointments(Carbon $start, Carbon $end): Collection
    {
        $user = Auth::user();

        $appointments = Appointment::query()
            ->where([
                'invitee_id' => $user->id,
                'invitee_type' => get_class($user),
            ])
            ->where('start_date', '>=', $start)
            ->where(function ($query) use ($end) {
                // If we have end_date in DB check endDate against it
                $query->where('end_date', '<=', $end)
                    // else we have to check if end_date is null and start_date is less than endDate
                    ->orWhere(fn ($q) => $q->whereNull('end_date')->where('start_date', '<=', $end));
            })
            ->with('linkable')
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return $appointments;
    }

    /**
     * Get calendar grid.
     *
     * @return array<array{date_iso: string, day: string, day_of_week: int, is_current_day: bool}>>
     */
    protected function getCalendarGrid(Carbon $start, Carbon $end): array
    {
        $days = [];

        // if we have a monthly calendar we need to start at the beginning of the week
        if ($this->calendarInterval === CalendarInterval::Monthly) {
            // get the start and end of the week
            $start = $start->startOfWeek();
            $end = $end->endOfWeek();
        }

        do {
            /** @var Carbon $day */
            $day = ! isset($day) ? (clone $start) : $day->addDay();

            $days[] = [
                'date_iso' => $day->format('Y-m-d'),
                'day' => $day->format('d'),
                'day_of_week' => $day->dayOfWeek,
                'is_current_day' => $day->isToday(),
            ];
        } while (! $end->isSameDay($day));

        return $days;
    }

    /**
     * Get date interval.
     *
     *
     * @return array{start: Carbon, end: Carbon}
     */
    protected function getDateInterval(CalendarInterval $calendarInterval): array
    {
        $currentDate = $this->current_date->copy();

        // get the interval based on the date
        return match ($calendarInterval) {
            CalendarInterval::Daily => [
                'start' => $currentDate->copy()->startOfDay(),
                'end' => $currentDate->copy()->endOfDay(),
            ],
            CalendarInterval::Weekly => [
                'start' => $currentDate->copy()->startOfWeek(),
                'end' => $currentDate->copy()->endOfWeek(),
            ],
            CalendarInterval::Monthly => [
                'start' => $currentDate->copy()->startOfMonth(),
                'end' => $currentDate->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get meta information.
     */
    protected function getMetaInformation(CalendarInterval $calendarInterval): CalendarMeta
    {
        $currentDate = $this->current_date->copy();

        return CalendarMeta::fromDateInterval($calendarInterval, $currentDate);
    }
}
