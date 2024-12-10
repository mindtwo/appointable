<?php

namespace mindtwo\Appointable\Data;

use Illuminate\Support\Carbon;
use mindtwo\Appointable\Enums\CalendarInterval;

class CalendarMeta
{
    public function __construct(
        public Carbon $previous,
        public Carbon $current,
        public Carbon $next,
        public ?Carbon $start = null,
        public ?Carbon $end = null,
    ) {}

    /**
     * Convert the object to an array.
     *
     * @return array<string, null|Carbon>
     */
    public function toArray(): array
    {
        return [
            'previous' => $this->previous,
            'current' => $this->current,
            'next' => $this->next,
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    public static function fromDateInterval(CalendarInterval $calendarInterval, Carbon $currentDate): self
    {
        $previous = match (
            $calendarInterval
        ) {
            CalendarInterval::Daily => $currentDate->copy()->subDay(),
            CalendarInterval::Weekly => $currentDate->copy()->subWeek(),
            CalendarInterval::Monthly => $currentDate->copy()->subMonth(),
        };

        $next = match (
            $calendarInterval
        ) {
            CalendarInterval::Daily => $currentDate->copy()->addDay(),
            CalendarInterval::Weekly => $currentDate->copy()->addWeek(),
            CalendarInterval::Monthly => $currentDate->copy()->addMonth(),
        };

        // get the interval based on the date
        $interval = match ($calendarInterval) {
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

        return new self(
            previous: $previous,
            current: $currentDate,
            next: $next,
            start: $interval['start'],
            end: $interval['end'],
        );
    }
}
