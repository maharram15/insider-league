<?php

namespace App\Services;

use App\Contracts\Services\LeagueServiceInterface;
use App\Models\FootballMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class LeagueService implements LeagueServiceInterface
{
    public function updateStandings(FootballMatch $match): void
    {
        if (!$match->isPlayed()) {
            return;
        }

        $week = $match->week;
        $homeTeamId = $match->home_team_id;
        $awayTeamId = $match->away_team_id;
        $homeGoals = $match->home_goals;
        $awayGoals = $match->away_goals;

        DB::transaction(function () use ($week, $homeTeamId, $awayTeamId, $homeGoals, $awayGoals) {
            $homePrevStanding = $this->getOrCreateStanding($homeTeamId, $week - 1);
            $awayPrevStanding = $this->getOrCreateStanding($awayTeamId, $week - 1);

            $homeCurrentStanding = $homePrevStanding->replicate(['id']);
            $homeCurrentStanding->week = $week;
            $homeCurrentStanding->played++;

            $awayCurrentStanding = $awayPrevStanding->replicate(['id']);
            $awayCurrentStanding->week = $week;
            $awayCurrentStanding->played++;

            $homeCurrentStanding->goals_for += $homeGoals;
            $homeCurrentStanding->goals_against += $awayGoals;
            $homeCurrentStanding->goal_difference = $homeCurrentStanding->goals_for - $homeCurrentStanding->goals_against;

            $awayCurrentStanding->goals_for += $awayGoals;
            $awayCurrentStanding->goals_against += $homeGoals;
            $awayCurrentStanding->goal_difference = $awayCurrentStanding->goals_for - $awayCurrentStanding->goals_against;

            if ($homeGoals > $awayGoals) {
                $homeCurrentStanding->won++;
                $homeCurrentStanding->points += 3;
                $awayCurrentStanding->lost++;
            } elseif ($homeGoals < $awayGoals) {
                $awayCurrentStanding->won++;
                $awayCurrentStanding->points += 3;
                $homeCurrentStanding->lost++;
            } else {
                $homeCurrentStanding->drawn++;
                $homeCurrentStanding->points += 1;
                $awayCurrentStanding->drawn++;
                $awayCurrentStanding->points += 1;
            }

            LeagueStanding::updateOrCreate(
                ['team_id' => $homeTeamId, 'week' => $week],
                $homeCurrentStanding->toArray()
            );

            LeagueStanding::updateOrCreate(
                ['team_id' => $awayTeamId, 'week' => $week],
                $awayCurrentStanding->toArray()
            );

            $teamsPlayedIds = [$homeTeamId, $awayTeamId];
            $teamsNotPlayed = Team::whereNotIn('id', $teamsPlayedIds)->get();

            foreach ($teamsNotPlayed as $team) {
                $existingStanding = LeagueStanding::where('team_id', $team->id)->where('week', $week)->first();
                if (!$existingStanding) {
                    $prevStanding = $this->getOrCreateStanding($team->id, $week - 1);
                    $currentStanding = $prevStanding->replicate(['id']);
                    $currentStanding->week = $week;
                    LeagueStanding::updateOrCreate(
                        ['team_id' => $team->id, 'week' => $week],
                        $currentStanding->toArray()
                    );
                }
            }

        });
    }

    /**
     * @param int $teamId
     * @param int $week
     * @return LeagueStanding
     */
    private function getOrCreateStanding(int $teamId, int $week): LeagueStanding
    {
        if ($week < 0) {
            return new LeagueStanding([
                'team_id' => $teamId,
                'week' => 0,
                'points' => 0, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0,
            ]);
        }

        $standing = LeagueStanding::where('team_id', $teamId)
            ->where('week', $week)
            ->first();

        if (!$standing) {
            $standing = LeagueStanding::where('team_id', $teamId)
                ->orderBy('week', 'desc')
                ->first();
        }

        if (!$standing) {
            return $this->getOrCreateStanding($teamId, -1);
        }

        return $standing;
    }


    public function getCurrentStandings()
    {
        $maxWeek = LeagueStanding::max('week') ?? 0;

        $allStandings = LeagueStanding::where('week', '<=', $maxWeek)
            ->with('team')
            ->orderBy('week', 'asc')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();

        return $allStandings->groupBy('week');
    }

    public function getStandingsForWeek(int $week)
    {
        return LeagueStanding::query()
            ->where('week', $week)
            ->with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();
    }

    public function getPlayedMatches()
    {
        return FootballMatch::query()
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week', 'asc')
            ->orderBy('played_at', 'asc')
            ->get();
    }

    public function initializeStandings(): void
    {
        if (LeagueStanding::where('week', 0)->exists()) {
            return;
        }

        $teams = Team::all();
        $initialStandings = [];
        $now = now();

        foreach ($teams as $team) {
            $initialStandings[] = [
                'team_id' => $team->id,
                'week' => 0,
                'points' => 0,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($initialStandings)) {
            LeagueStanding::insert($initialStandings);
        }
    }

}
