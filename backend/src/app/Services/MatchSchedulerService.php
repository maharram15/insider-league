<?php

namespace app\Services;

use App\Contracts\MatchSchedulerInterface;
use App\Exceptions\SimulationException;
use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class MatchSchedulerService implements MatchSchedulerInterface
{
    private ?int $totalWeeks = null;

    public function scheduleMatchesForWeek(int $week)
    {
        if (FootballMatch::where('week', $week)->exists()) {
            return FootballMatch::where('week', $week)->with(['homeTeam', 'awayTeam'])->get();
        }

        if (!$this->isScheduleGenerated()) {
            $this->generateFullSchedule();
        }

        $matches = FootballMatch::where('week', $week)->with(['homeTeam', 'awayTeam'])->get();

        return $matches->isNotEmpty() ? $matches : null;
    }

    public function getTotalWeeks(): int
    {
        if ($this->totalWeeks === null) {
            $teamsCount = Team::count();
            $this->totalWeeks = ($teamsCount % 2 === 0) ? ($teamsCount - 1) * 2 : $teamsCount * 2;
        }
        return $this->totalWeeks;
    }

    public function isScheduleGenerated(): bool
    {
        $totalWeeks = $this->getTotalWeeks();
        if ($totalWeeks == 0) return true;
        return FootballMatch::where('week', $totalWeeks)->exists();
    }


    public function generateFullSchedule(): void
    {
        if ($this->isScheduleGenerated()) {
            return;
        }

        $teams = Team::all()->shuffle();
        $teamsCount = $teams->count();

        if ($teamsCount < 2) {
            throw new SimulationException("Cannot generate schedule: Not enough teams (found {$teamsCount}).");
        }

        $isOdd = $teamsCount % 2 !== 0;
        if ($isOdd) {
            $teams->push(null);
            $teamsCount++;
        }

        $matchesToInsert = [];
        $totalWeeks = $this->getTotalWeeks();
        $rounds = $totalWeeks / 2;


        $teamIds = $teams->map(fn($team) => $team ? $team->id : null)->toArray();


        for ($round = 0; $round < $rounds; $round++) {
            $week1 = $round + 1;
            $week2 = $round + 1 + $rounds;

            for ($i = 0; $i < $teamsCount / 2; $i++) {
                $homeTeamId = $teamIds[$i];
                $awayTeamId = $teamIds[$teamsCount - 1 - $i];

                if ($homeTeamId === null || $awayTeamId === null) {
                    continue;
                }

                $now = now();

                $matchesToInsert[] = [
                    'week' => $week1,
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                    'home_goals' => null,
                    'away_goals' => null,
                    'played_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $matchesToInsert[] = [
                    'week' => $week2,
                    'home_team_id' => $awayTeamId,
                    'away_team_id' => $homeTeamId,
                    'home_goals' => null,
                    'away_goals' => null,
                    'played_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $fixedTeamId = $teamIds[0];
            $rotatedTeams = array_slice($teamIds, 1);
            $lastTeamId = array_pop($rotatedTeams);
            array_unshift($rotatedTeams, $lastTeamId);
            $teamIds = array_merge([$fixedTeamId], $rotatedTeams);

        }

        if (!empty($matchesToInsert)) {
            FootballMatch::insert($matchesToInsert);
        } else {
            throw new SimulationException("No matches were generated for insertion..");
        }
    }
}
