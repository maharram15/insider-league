<?php

return [
    'basic_simulator' => [
        'base_goals_factor' => env('SIM_BASE_GOALS', 0.05),
        'strength_factor' => env('SIM_STRENGTH_FACTOR', 0.015),
        'home_advantage' => env('SIM_HOME_ADVANTAGE', 0.1),
        'max_goals_per_team' => env('SIM_MAX_GOALS', 6),
    ],
];
