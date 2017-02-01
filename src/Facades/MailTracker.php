<?php

namespace Jeylabs\MailTracker\Facades;

use Illuminate\Support\Facades\Facade;

class MailTracker extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MailTracker';
    }
}