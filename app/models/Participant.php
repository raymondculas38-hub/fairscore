<?php

class Participant extends Model
{
    protected static $table = 'participants';

    public static function findOrFail($id): self
    {
        $record = self::find($id);
        if (!$record) {
            http_response_code(404);
            die("Participant not found.");
        }
        return $record;
    }
}
