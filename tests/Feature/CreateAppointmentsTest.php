<?php

use Workbench\App\Models\User;

test('create an appointment', function (array $data, int $expect_status) {
    $user = User::factory()->create();

    $this->actingAs($user);

    // check if the route exists
    $routes = collect(app('router')->getRoutes()->getRoutesByName());
    expect($routes->has('appointments.store'))->toBeTrue();

    $response = $this->postJson(route('appointments.store'), $data);

    $response->assertStatus($expect_status);
})->with([
    [
        [
            'title' => 'Test Appointment',
            'start' => now()->addDay()->format('Y-m-d H:i:s'),
            'end' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ],
        201,
    ],
    [
        [
            'title' => 'Test Appointment',
            'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ],
        201,
    ],
    [
        [
            'title' => 'Test Appointment',
            'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ],
        201,
    ],
    [
        [
            'title' => 'Test Appointment',
        ],
        422,
    ],
    [
        [
            'title' => 'Test Appointment',
            'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHour()->format('H:i:s'),
            'location' => 'Test Location',
            'description' => 'Test Description',
        ],
        422,
    ],
]);

it('splits start and end into date and time', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $start = now()->addDay();
    $end = now()->addDay()->addHour();

    $data = [
        'title' => 'Test Appointment',
        'start' => $start->format('Y-m-d H:i:s'),
        'end' => $end->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(route('appointments.store'), $data);

    $response->assertStatus(201);

    $response->assertJsonFragment([
        'start_date' => $start->format('Y-m-d'),
        'end_date' => $end->format('Y-m-d'),
        'start_time' => $start->format('H:i:s'),
        'end_time' => $end->format('H:i:s'),
    ]);
});

it('ignores start_time and end_time if we have an entire day', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $data = [
        'title' => 'Test Appointment',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->addHour()->format('Y-m-d'),
        'is_entire_day' => true,
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
        'location' => 'Test Location',
        'description' => 'Test Description',
    ];

    $response = $this->postJson(route('appointments.store'), $data);

    $response->assertStatus(201);

    $response->assertJsonFragment([
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'start_time' => null,
        'end_time' => null,
        'is_entire_day' => true,
    ]);
});
