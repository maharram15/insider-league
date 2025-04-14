<?php

namespace App\Contracts\Services;

use App\Models\FootballMatch;

interface LeagueServiceInterface
{
    /**
     * @param FootballMatch $match
     * @return void
     */
    public function updateStandings(FootballMatch $match): void;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrentStandings();

    /**
     * @param int $week
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStandingsForWeek(int $week);


    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlayedMatches();

    /**
     * @return void
     */
    public function initializeStandings(): void;

}
