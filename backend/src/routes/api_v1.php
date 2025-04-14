<?php

use App\Http\Controllers\Api\V1\LeagueSimulationController;
use Illuminate\Support\Facades\Route;

Route::prefix('/league')->name('api.v1.league.')->group(function () {
    Route::get('/results', [LeagueSimulationController::class, 'getResults'])->name('results');
    Route::post('/simulate/next', [LeagueSimulationController::class, 'simulateNextWeek'])->name('simulate.next');
    Route::post('/simulate/all', [LeagueSimulationController::class, 'simulateAllRemainingWeeks'])->name('simulate.all');
    Route::post('/reset', [LeagueSimulationController::class, 'resetSimulation'])->name('reset');
});




