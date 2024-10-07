<?php

use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Workbench\App\Models\CalendarAppointment;
use Workbench\App\Models\User;

test('list all appointments', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $currentMonth = now()->month;
    $currentYear = now()->year;

    CalendarAppointment::factory()
        ->count(10)
        ->for($user)
        ->date($currentYear, $currentMonth)
        ->create();

    expect(CalendarAppointment::whereMonth('start', $currentMonth)->count())->toBe(10);

    $response = $this->get(route('appointments.index'))
        ->assertStatus(200);

    $response->assertJson(function (AssertableJson $json) {
        $json
            ->has('data', function ($json) {
                $json
                    ->whereAllType([
                        'current_date' => 'string',
                        'calendar_interval' => 'string',
                        'current_day' => 'integer',
                        'current_week' => 'integer',
                        'current_month' => 'integer',
                        'current_year' => 'integer',
                        'days' => 'array',
                        'appointments' => 'array',
                        'month_names' => 'array',
                        'week_days' => 'array',
                    ])
                    ->etc();
            })->has('meta');
    });

    $appointments = $response->json('data.appointments');

    expect(collect($appointments)->flatten(1))->toHaveCount(10);
});

it('lists all appointments if we switch date parameter', function ($createDates, $created, $expectForDate, $dateParam) {
    $user = User::factory()->create();

    $this->actingAs(User::first());

    $createDates($user);

    expect(CalendarAppointment::count())->toBe($created);

    $response = $this->get(route('appointments.index', [
        'date' => $dateParam->format('Y-m-d'),
    ]))
        ->assertStatus(200);

    $response->assertJson(function (AssertableJson $json) {
        $json
            ->has('data', function ($json) {
                $json
                    ->whereAllType([
                        'current_date' => 'string',
                        'calendar_interval' => 'string',
                        'current_day' => 'integer',
                        'current_week' => 'integer',
                        'current_month' => 'integer',
                        'current_year' => 'integer',
                        'days' => 'array',
                        'appointments' => 'array',
                        'month_names' => 'array',
                        'week_days' => 'array',
                    ])
                    ->etc();
            })->has('meta');
    });

    $appointments = collect($response->json('data.appointments'));
    expect($appointments->flatten(1))->toHaveCount($expectForDate);

    // Check if the appointments are from the curent month and if they are grouped correctly
    $appointments->each(function ($appointment, $key) use ($dateParam) {

        $date = Carbon::parse($key);

        expect($date->month)->toBe($dateParam->month);

        foreach ($appointment as $app) {
            $start = Carbon::parse($app['start_date']);
            expect($start->day)->toBe($date->day);
            expect($start->month)->toBe($date->month);
            expect($start->year)->toBe($date->year);
        }
    });
})->with([
    [
        'createDates' => function ($user) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            CalendarAppointment::factory()
                ->count(4)
                ->for($user)
                ->date($currentYear, $currentMonth, 15)
                ->create();

            CalendarAppointment::factory()
                ->count(3)
                ->for($user)
                ->date($currentYear, $currentMonth - 1)
                ->create();

            CalendarAppointment::factory()
                ->count(3)
                ->for($user)
                ->date($currentYear, $currentMonth + 1, 15)
                ->create();
        },
        'created' => 10,
        'expectForDate' => 3,
        'date' => now()->month(now()->month - 1),
    ],
    [
        'createDates' => function ($user) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            CalendarAppointment::factory()
                ->count(4)
                ->for($user)
                ->date($currentYear, $currentMonth, 15)
                ->create();

            CalendarAppointment::factory()
                ->count(3)
                ->for($user)
                ->date($currentYear, $currentMonth - 1, 15)
                ->create();

            CalendarAppointment::factory()
                ->count(3)
                ->for($user)
                ->date($currentYear, $currentMonth + 1)
                ->create();
        },
        'created' => 10,
        'expectForDate' => 3,
        'date' => now()->month(now()->month + 1),
    ],
]);

test('month overflows', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // test if the dates/months are correctly handled with overflows
    $currentMonth = 10;
    $currentYear = 2024;

    // october 2024 has an overflow to november 2024
    CalendarAppointment::factory()
        ->count(4)
        ->for($user)
        ->date($currentYear, $currentMonth)
        ->create();

    CalendarAppointment::factory()
        ->count(3)
        ->for($user)
        ->date($currentYear, $currentMonth - 1, 15)
        ->create();

    // 1st november 2024 should be included
    CalendarAppointment::factory()
        ->for($user)
        ->date($currentYear, $currentMonth + 1, 1)
        ->create();

    CalendarAppointment::factory()
        ->for($user)
        ->date($currentYear, $currentMonth + 1, 6)
        ->create();

    expect(CalendarAppointment::count())->toBe(9);

    $response = $this->get(route('appointments.index', [
        'date' => now()->format('Y-m-d'),
    ]))
        ->assertStatus(200);

    $response->assertJson(function (AssertableJson $json) {
        $json
            ->has('data', function ($json) {
                $json
                    ->whereAllType([
                        'current_date' => 'string',
                        'calendar_interval' => 'string',
                        'current_day' => 'integer',
                        'current_week' => 'integer',
                        'current_month' => 'integer',
                        'current_year' => 'integer',
                        'days' => 'array',
                        'appointments' => 'array',
                        'month_names' => 'array',
                        'week_days' => 'array',
                    ])
                    ->etc();
            })->has('meta');
    });

    $appointments = collect($response->json('data.appointments'));
    expect($appointments->flatten(1))->toHaveCount(5);

    // Check if the appointments are from the curent month and if they are grouped correctly
    $appointments->each(function ($appointment, $key) {
        $currentMonth = now()->month;
        $date = Carbon::parse($key);

        expect($date->month)->toBeIn([$currentMonth, $currentMonth + 1]);

        foreach ($appointment as $app) {
            $start = Carbon::parse($app['start_date']);
            expect($start->day)->toBe($date->day);
            expect($start->month)->toBe($date->month);
            expect($start->year)->toBe($date->year);
        }
    });
});

test('weekly interval', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $currentMonth = now()->month;
    $currentYear = now()->year;

    CalendarAppointment::factory()
        ->count(10)
        ->for($user)
        ->date($currentYear, $currentMonth)
        ->create();

    $thisWeek = CalendarAppointment::whereBetween('start', [
        now()->startOfWeek(),
        now()->endOfWeek(),
    ])->get();

    $response = $this->get(route('appointments.index', [
        'interval' => 'weekly',
    ]))
        ->assertStatus(200);

    $response->assertJson(function (AssertableJson $json) {
        $json
            ->has('data', function ($json) {
                $json
                    ->whereAllType([
                        'current_date' => 'string',
                        'calendar_interval' => 'string',
                        'current_day' => 'integer',
                        'current_week' => 'integer',
                        'current_month' => 'integer',
                        'current_year' => 'integer',
                        'days' => 'array',
                        'appointments' => 'array',
                        'month_names' => 'array',
                        'week_days' => 'array',
                    ])
                    ->etc();
            })->has('meta');
    });

    $appointments = collect($response->json('data.appointments'));

    expect($appointments->flatten(1))->toHaveCount($thisWeek->count());
});

test('daily interval', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $currentMonth = now()->month;
    $currentYear = now()->year;

    CalendarAppointment::factory()
        ->count(10)
        ->for($user)
        ->date($currentYear, $currentMonth)
        ->create();

    CalendarAppointment::factory()
        ->count(2)
        ->for($user)
        ->date($currentYear, $currentMonth, now()->day)
        ->create();

    $today = CalendarAppointment::whereDate('start', now())->get();

    $response = $this->get(route('appointments.index', [
        'interval' => 'daily',
    ]))
        ->assertStatus(200);

    $response->assertJson(function (AssertableJson $json) {
        $json
            ->has('data', function ($json) {
                $json
                    ->whereAllType([
                        'current_date' => 'string',
                        'calendar_interval' => 'string',
                        'current_day' => 'integer',
                        'current_week' => 'integer',
                        'current_month' => 'integer',
                        'current_year' => 'integer',
                        'days' => 'array',
                        'appointments' => 'array',
                        'month_names' => 'array',
                        'week_days' => 'array',
                    ])
                    ->etc();
            })->has('meta');
    });

    $appointments = collect($response->json('data.appointments'));

    expect($appointments->flatten(1))->toHaveCount($today->count());
});
