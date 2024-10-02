<?php

namespace mindtwo\Appointable\Facades;

use Illuminate\Support\Facades\Facade;
use mindtwo\Appointable\Services\Appointable as ServicesAppointable;

class Appointable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ServicesAppointable::class;
    }
}
