<?php

namespace App\Providers;

use App\Events\TaskCreatedEvent;
use App\Events\TaskDeleteEvent;
use App\Events\TaskUpdateEvent;
use App\Events\UserCreateEvent;
use App\Listeners\TaskCreatedLoggerListener;
use App\Listeners\TaskDeleteLoggerListener;
use App\Listeners\TaskUpdateLoggerListener;
use App\Listeners\UserCreateLoggerListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];
}
