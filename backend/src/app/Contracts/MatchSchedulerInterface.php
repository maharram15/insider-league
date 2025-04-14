<?php

namespace app\Contracts;

interface MatchSchedulerInterface
{
    /**
     * @param int $week
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function scheduleMatchesForWeek(int $week);

    /**
     * @return int
     */
    public function getTotalWeeks(): int;

    /**
     * @return bool
     */
    public function isScheduleGenerated(): bool;

    /**
     * @return void
     */
    public function generateFullSchedule(): void;
}
