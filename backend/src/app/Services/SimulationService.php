<?php

namespace app\Services;

use App\Contracts\MatchSchedulerInterface;
use App\Contracts\MatchSimulatorInterface;
use App\Contracts\Services\LeagueServiceInterface;
use App\DTO\SimulationResultDto;
use App\Exceptions\SimulationException;
use App\Models\FootballMatch;
use App\Models\LeagueStanding;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimulationService
{
    protected MatchSchedulerInterface $scheduler;
    protected MatchSimulatorInterface $simulator;
    protected LeagueServiceInterface $leagueService;

    public function __construct(
        MatchSchedulerInterface $scheduler,
        MatchSimulatorInterface $simulator,
        LeagueServiceInterface $leagueService
    ) {
        $this->scheduler = $scheduler;
        $this->simulator = $simulator;
        $this->leagueService = $leagueService;
    }

    /**
     * @return int|null
     */
    public function getNextWeekToSimulate(): ?int
    {
        $lastPlayedWeek = FootballMatch::whereNotNull('played_at')->max('week');

        if ($lastPlayedWeek === null) {
            if (!$this->scheduler->isScheduleGenerated()) {
                $this->scheduler->generateFullSchedule();
            }
            $this->leagueService->initializeStandings();
            return 1;
        }

        $totalWeeks = $this->scheduler->getTotalWeeks();
        $nextWeek = $lastPlayedWeek + 1;

        return ($nextWeek <= $totalWeeks) ? $nextWeek : null;
    }

    /**
     * @return array
     */
    public function simulateNextWeek(): SimulationResultDto
    {
        $nextWeek = $this->getNextWeekToSimulate();

        if ($nextWeek === null) {
            return new SimulationResultDto(
                success: false,
                message: 'All weeks have already been played.',
                weekSimulated: null,
                statusCode: 400
            );
        }

        $matches = $this->scheduler->scheduleMatchesForWeek($nextWeek);

        if ($matches === null || $matches->isEmpty()) {
            Log::error("Failed to retrieve matches for week {$nextWeek}.");
            return new SimulationResultDto(
                success: false,
                message: "Failed to retrieve or generate matches for week {$nextWeek}",
                weekSimulated: null,
                statusCode: 500
            );
        }

        DB::beginTransaction();
        try {
            foreach ($matches as $match) {
                if ($match->isPlayed()) {
                    continue;
                }

                [$homeGoals, $awayGoals] = $this->simulator->simulate($match->homeTeam, $match->awayTeam);

                $match->home_goals = $homeGoals ?? 0;
                $match->away_goals = $awayGoals ?? 0;
                $match->played_at = now();
                $match->save();

                $this->leagueService->updateStandings($match);
            }

            DB::commit();
            $successMessage = "Week {$nextWeek} successfully simulated.";

            return new SimulationResultDto(
                success: true,
                message: $successMessage,
                weekSimulated: $nextWeek,
                statusCode: 200
            );


        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error during simulation of week {$nextWeek}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return new SimulationResultDto(
                success: false,
                message: $e->getMessage(),
                weekSimulated: null,
                statusCode: 500
            );
        }
    }

    /**
     * @return array
     */
    public function simulateAllRemainingWeeks(): array
    {
        $simulatedWeeks = [];
        $totalWeeks = $this->scheduler->getTotalWeeks();

        if (!$this->scheduler->isScheduleGenerated()) {
            $this->scheduler->generateFullSchedule();
        }
        $this->leagueService->initializeStandings();


        while (true) {
            $nextWeek = $this->getNextWeekToSimulate();
            if ($nextWeek === null) {
                break;
            }

            if ($nextWeek > $totalWeeks) {
                throw new SimulationException("Attempting to simulate week {$nextWeek}, but only {$totalWeeks} weeks exist.");
            }

            $result = $this->simulateNextWeek();

            if ($result['success']) {
                $simulatedWeeks[] = $result['week_simulated'];
            } else {
                return [
                    'success' => false,
                    'message' => "Error simulating week {$nextWeek}. " . $result['message'],
                    'weeks_simulated' => $simulatedWeeks
                ];
            }
        }

        if (empty($simulatedWeeks)) {
            return ['success' => true, 'message' => 'No weeks to simulate, all have already been played.', 'weeks_simulated' => []];
        }

        return [
            'success' => true,
            'message' => 'All remaining weeks successfully simulated.',
            'weeks_simulated' => $simulatedWeeks
        ];
    }

    /**
     * @return array
     */
    public function resetSimulation(): SimulationResultDto
    {
        DB::beginTransaction();
        try {
            $updatedMatches = FootballMatch::whereNotNull('played_at')
                ->update([
                    'home_goals' => null,
                    'away_goals' => null,
                    'played_at' => null,
                ]);
            Log::info("Reset match results: {$updatedMatches}");


            $deletedStandings = LeagueStanding::where('week', '>', 0)->delete();
            Log::info("Standings deleted (week > 0): {$deletedStandings}");


            LeagueStanding::where('week', 0)
                ->update([
                    'points' => 0,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'goal_difference' => 0,
                ]);

            DB::commit();
            return new SimulationResultDto(
                success: true,
                message: 'Simulation reset successfully.',
                weekSimulated: null,
                statusCode: 200
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return SimulationException::handle($e, 'Error during simulation reset');
        }
    }
}
