<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Workbench\App\Models\CalendarAppointment;

/**
 * @template TModel of \Workbench\App\Models\CalendarAppointment
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class CalendarAppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = CalendarAppointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTime;

        $end = Carbon::parse($start)->addHours(1);

        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Indicate that the user is suspended.
     */
    public function date(?int $year = null, ?int $month = null, ?int $day = null): Factory
    {
        return $this->state(function (array $attributes) use ($year, $month, $day) {
            $start = Carbon::parse($this->faker->dateTime);

            $start->setTime(0, 0, 0);
            if ($day) {
                $start->day($day);
            }

            if ($month) {
                if ($start->isLastOfMonth()) {
                    $start->day(1);
                    $start->month($month);

                    $start->endOfMonth();
                    $start->startOfDay();
                } else {
                    $start->month($month);
                }
            }

            if ($year) {
                $start->year($year);
            }

            $end = Carbon::parse($start)->addHours(1);

            return [
                'start' => $start,
                'end' => $end,
            ];
        });
    }
}
