<?php

namespace App\DTO;

readonly class SimulationResultDto
{
    public function __construct(
        public bool   $success,
        public string $message,
        public ?int   $weekSimulated,
        public ?int   $statusCode = null
    ) {}

}
