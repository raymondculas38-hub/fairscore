<?php

class Event extends Model
{
    protected static $table = 'events';

    public static function scopedAll(): array
    {
        $adminId = $_SESSION['user_id'] ?? null;
        if (!$adminId) return [];
        $stmt = self::getDb()->prepare("SELECT * FROM events WHERE admin_id = ? ORDER BY created_at DESC");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function scopedFindOrFail($id): self
    {
        $adminId = $_SESSION['user_id'] ?? null;
        $stmt = self::getDb()->prepare("SELECT * FROM events WHERE id = ? AND admin_id = ?");
        $stmt->execute([$id, $adminId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $record = $stmt->fetch();
        if (!$record) {
            http_response_code(404);
            die("Event not found or access denied.");
        }
        return $record;
    }

    public function participants(): array
    {
        $stmt = self::getDb()->prepare("SELECT * FROM participants WHERE event_id = ? ORDER BY contestant_number ASC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Participant::class);
    }

    public function criteria(): array
    {
        $stmt = self::getDb()->prepare("SELECT * FROM criteria WHERE event_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Criteria::class);
    }

    public function judges(): array
    {
        $stmt = self::getDb()->prepare("
            SELECT users.* FROM users
            JOIN event_judge ON users.id = event_judge.judge_id
            WHERE event_judge.event_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, User::class);
    }

    public function scores(): array
    {
        $stmt = self::getDb()->prepare("SELECT * FROM scores WHERE event_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Score::class);
    }

    public function syncJudges(array $judgeIds): void
    {
        $db = self::getDb();
        $db->prepare("DELETE FROM event_judge WHERE event_id = ?")->execute([$this->id]);
        $stmt = $db->prepare("INSERT INTO event_judge (event_id, judge_id) VALUES (?, ?)");
        foreach ($judgeIds as $judgeId) {
            $stmt->execute([$this->id, $judgeId]);
        }
    }
}
