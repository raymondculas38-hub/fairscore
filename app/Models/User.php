<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'address',
        'password',
        'role',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isJudge(): bool
    {
        return $this->role === 'JUDGE';
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_judge', 'judge_id', 'event_id')->withTimestamps();
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'judge_id');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'admin_id');
    }
}
