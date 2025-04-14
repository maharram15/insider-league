<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\LeagueServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeagueStandingResource;
use app\Models\LeagueStanding;
use App\Services\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeagueSimulationController extends Controller
{
    protected LeagueServiceInterface $leagueService;
    protected SimulationService $simulationService;

    public function __construct(LeagueServiceInterface $leagueService, SimulationService $simulationService)
    {
        $this->leagueService = $leagueService;
        $this->simulationService = $simulationService;
    }

    public function getResults(): JsonResponse
    {
        $groupedStandings = $this->leagueService->getCurrentStandings();
        $playedMatches = $this->leagueService->getPlayedMatches();
        $standingsByWeekResource = $groupedStandings->mapWithKeys(function ($weeklyStandings, $weekNumber) {
            return [$weekNumber => LeagueStandingResource::collection($weeklyStandings)];
        });
        $maxWeek = LeagueStanding::max('week') ?? 0;

        return response()->json([
            'current_week' => $maxWeek,
            'current_standings' => $standingsByWeekResource,
            'played_matches' => $playedMatches,
        ]);
    }

    public function simulateNextWeek(): JsonResponse
    {
        $result = $this->simulationService->simulateNextWeek();

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'week_simulated' => $result['week_simulated']
            ]);
        }

        $statusCode = str_contains($result['message'], 'Все недели уже сыграны') || str_contains($result['message'], 'Не удалось получить матчи') ? 400 : 500;
        return response()->json(['message' => $result['message']], $statusCode);
    }

    public function simulateAllRemainingWeeks(): JsonResponse
    {
        $result = $this->simulationService->simulateAllRemainingWeeks();

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'weeks_simulated' => $result['weeks_simulated']
            ]);
        }
        return response()->json(['message' => $result['message']], 500);
    }

    public function resetSimulation(Request $request): JsonResponse
    {
        $result = $this->simulationService->resetSimulation();

        if ($result['success']) {
            return response()->json(['message' => $result['message']]);
        }

        return response()->json(['message' => $result['message']], 500);
    }

}
