<?php

namespace Database\Seeders;

use App\Contracts\Services\LeagueServiceInterface;
use Illuminate\Database\Seeder;

class LeagueStandingSeeder extends Seeder
{
    protected LeagueServiceInterface $leagueService;

    public function __construct(LeagueServiceInterface $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    /**
     * Run the database seeds.
     *
     * Этот сидер отвечает за создание начального состояния
     * турнирной таблицы (Week 0) для всех команд.
     * Он использует существующий метод initializeStandings из LeagueService.
     *
     * @return void
     */
    public function run(): void
    {
        $this->leagueService->initializeStandings();

        $this->command->info('League standings initialized for Week 1.');
    }
}
