<?php

namespace app\Contracts;

use App\Models\Team;

interface MatchSimulatorInterface
{
    /**
     * @param Team $homeTeam
     * @param Team $awayTeam
     * @return array<int>
     */
    public function simulate(Team $homeTeam, Team $awayTeam): array;

}
