<?php

class User extends Model
{
    protected static $table = 'users';

    public function isAdmin(): bool
    {
        return strtoupper($this->role ?? '') === 'ADMIN';
    }

    public function isJudge(): bool
    {
        return strtoupper($this->role ?? '') === 'JUDGE';
    }

    public function events(): array
    {
        $stmt = self::getDb()->prepare("
            SELECT events.* FROM events
            JOIN event_judge ON events.id = event_judge.event_id
            WHERE event_judge.judge_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Event::class);
    }
}
