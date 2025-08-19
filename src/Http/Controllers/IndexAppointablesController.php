<?php

namespace mindtwo\Appointable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use mindtwo\Appointable\Actions\IndexAppointables;
use mindtwo\Appointable\Contracts\Invitee;
use mindtwo\Appointable\Enums\CalendarInterval;
use mindtwo\Appointable\Helper\TimesAndDates;

class IndexAppointablesController
{
    /**
     * Index all appointables.
     */
    public function __invoke(Request $request, IndexAppointables $indexAppointables): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user || ! $user instanceof Invitee, 401);

        if ($request->get('date', false)) {
            $date = TimesAndDates::convertDateToUserTimezone($request->get('date'));
        } else {
            $date = Carbon::now();
        }

        $interval = $request->enum('interval', CalendarInterval::class) ?? CalendarInterval::Monthly;

        return response()->json(
            $indexAppointables($date, $interval, $user)
        );
    }
}
