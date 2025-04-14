<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Team;

class LeagueStanding extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id',
        'week',
        'points',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

}
