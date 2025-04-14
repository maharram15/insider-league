<?php

namespace App\Providers;

use App\Contracts\MatchSchedulerInterface;
use App\Contracts\MatchSimulatorInterface;
use App\Contracts\Services\LeagueServiceInterface;
use App\Services\BasicMatchSimulatorService;
use App\Services\LeagueService;
use App\Services\MatchSchedulerService;
use App\Services\SimulationService;
use Illuminate\Support\ServiceProvider;

class SimulationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(MatchSimulatorInterface::class, BasicMatchSimulatorService::class);
        $this->app->bind(LeagueServiceInterface::class, LeagueService::class);
        $this->app->bind(MatchSchedulerInterface::class, MatchSchedulerService::class);

        $this->app->singleton(SimulationService::class, function ($app) {
            return new SimulationService(
                $app->make(MatchSchedulerInterface::class),
                $app->make(MatchSimulatorInterface::class),
                $app->make(LeagueServiceInterface::class)
            );
        });
    }

    /**
     * @return void
     */
    public function boot()
    {
        //
    }

}
