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

it('lists all appointments if we switch date parameter', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $currentMonth = now()->month;
    $currentYear = now()->year;

    CalendarAppointment::factory()
        ->count(4)
        ->for($user)
        ->date($currentYear, $currentMonth)
        ->create();

    CalendarAppointment::factory()
        ->count(3)
        ->for($user)
        ->date($currentYear, $currentMonth - 1)
        ->create();

    expect(CalendarAppointment::count())->toBe(7);

    $response = $this->get(route('appointments.index', [
        'date' => now()->month($currentMonth - 1)->format('Y-m-d'),
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

    expect($appointments->flatten(1))->toHaveCount(3);

    // Check if the appointments are from the curent month and if they are grouped correctly
    $appointments->each(function ($appointment, $key) use ($currentMonth) {

        $date = Carbon::parse($key);

        expect($date->month)->toBe($currentMonth - 1);

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
