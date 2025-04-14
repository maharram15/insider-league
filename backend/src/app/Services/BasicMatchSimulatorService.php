<?php

namespace App\Services;

use App\Contracts\MatchSimulatorInterface;
use App\Models\Team;

class BasicMatchSimulatorService implements MatchSimulatorInterface
{

    private const BASE_GOALS_FACTOR = 0.05;
    private const STRENGTH_FACTOR = 0.015;
    private const HOME_ADVANTAGE = 0.1;
    private const MAX_GOALS_PER_TEAM = 6;

    public function simulate(Team $homeTeam, Team $awayTeam): array
    {
        $homeStrength = $homeTeam->strength;
        $awayStrength = $awayTeam->strength;

        $homeExpectedGoals = self::BASE_GOALS_FACTOR
            + ($homeStrength * self::STRENGTH_FACTOR)
            + (($homeStrength - $awayStrength) * self::STRENGTH_FACTOR / 2)
            + self::HOME_ADVANTAGE;

        $awayExpectedGoals = self::BASE_GOALS_FACTOR
            + ($awayStrength * self::STRENGTH_FACTOR)
            + (($awayStrength - $homeStrength) * self::STRENGTH_FACTOR / 2);

        $homeGoals = $this->generateGoals($homeExpectedGoals);
        $awayGoals = $this->generateGoals($awayExpectedGoals);

        return [
            min($homeGoals, self::MAX_GOALS_PER_TEAM),
            min($awayGoals, self::MAX_GOALS_PER_TEAM),
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
