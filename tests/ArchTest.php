<?php

arch('globals dd')
    ->expect('mindtwo\Appointable')
    ->not->toUse('dd');

arch('globals dump')
    ->expect('mindtwo\Appointable')
    ->not->toUse('dump');

arch('globals ray')
    ->expect('mindtwo\Appointable')
    ->not->toUse('ray');
