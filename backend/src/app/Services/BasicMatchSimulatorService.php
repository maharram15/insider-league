<?php

namespace App\Services;

use App\Contracts\MatchSimulatorInterface;
use App\Models\Team;

class BasicMatchSimulatorService implements MatchSimulatorInterface
{

    private readonly float $baseGoalsFactor;
    private readonly float $strengthFactor;
    private readonly float $homeAdvantage;
    private readonly int $maxGoalsPerTeam;

    public function __construct()
    {
        $this->baseGoalsFactor = (float) config('simulation.basic_simulator.base_goals_factor', 0.05);
        $this->strengthFactor = (float) config('simulation.basic_simulator.strength_factor', 0.015);
        $this->homeAdvantage = (float) config('simulation.basic_simulator.home_advantage', 0.1);
        $this->maxGoalsPerTeam = (int) config('simulation.basic_simulator.max_goals_per_team', 6);
    }

    public function simulate(Team $homeTeam, Team $awayTeam): array
    {
        $homeStrength = $homeTeam->strength;
        $awayStrength = $awayTeam->strength;

        $homeExpectedGoals = $this->baseGoalsFactor
            + ($homeStrength * $this->strengthFactor)
            + (($homeStrength - $awayStrength) * $this->strengthFactor / 2)
            + $this->homeAdvantage;

        $awayExpectedGoals = $this->baseGoalsFactor
            + ($awayStrength * $this->strengthFactor)
            + (($awayStrength - $homeStrength) * $this->strengthFactor / 2);

        $homeGoals = $this->generateGoals($homeExpectedGoals);
        $awayGoals = $this->generateGoals($awayExpectedGoals);

        return [
            min($homeGoals, $this->maxGoalsPerTeam),
            min($awayGoals, $this->maxGoalsPerTeam),
        ];
    }

    /**
     * @param float $expectedGoals
     * @return int
     */
    private function generateGoals(float $expectedGoals): int
    {
        $lambda = max(0.1, $expectedGoals * 2.5);
        $l = exp(-$lambda);
        $k = 0;
        $p = 1.0;
        do {
            $k++;
            $p *= random_int(0, mt_getrandmax()) / mt_getrandmax();
        } while ($p > $l);

        return $k - 1;
    }

}
