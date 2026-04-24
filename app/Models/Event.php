<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'name', 'description', 'event_date', 'status', 'pin',
                           'public_display_mode', 'public_criteria_id'];

    protected static function booted()
    {
        static::addGlobalScope('admin', function (\Illuminate\Database\Eloquent\Builder $builder) {
            // Apply isolation scope ONLY if the logged-in user is an ADMIN
            if (auth()->check() && auth()->user()->role === 'ADMIN') {
                $builder->where('admin_id', auth()->id());
            }
        });
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function criteria()
    {
        return $this->hasMany(Criteria::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function judges()
    {
        return $this->belongsToMany(User::class, 'event_judge', 'event_id', 'judge_id')->withTimestamps();
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'live'      => 'bg-green-500/30 text-green-300 border border-green-500/50 font-bold',
            'completed' => 'bg-slate-500/30 text-slate-200 border border-slate-500/50 font-bold',
            default     => 'bg-amber-500/30 text-amber-300 border border-amber-500/50 font-bold',
        };
    }
}
