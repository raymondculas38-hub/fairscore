<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'judge_id', 'participant_id', 'criteria_id', 'score'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
