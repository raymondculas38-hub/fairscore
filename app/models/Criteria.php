<?php

class Criteria extends Model
{
    protected static $table = 'criteria';

    public static function findOrFail($id): self
    {
        $record = self::find($id);
        if (!$record) {
            http_response_code(404);
            die("Criteria not found.");
        }
        return $record;
    }
}
