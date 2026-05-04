<?php

abstract class Model
{
    protected static $table;
    protected static $primaryKey = 'id';

    public static function getDb()
    {
        return Database::getInstance()->getConnection();
    }

    public static function all()
    {
        $stmt = self::getDb()->query("SELECT * FROM `" . static::$table . "`");
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public static function find($id)
    {
        $stmt = self::getDb()->prepare("SELECT * FROM `" . static::$table . "` WHERE `" . static::$primaryKey . "` = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);
        return $stmt->fetch();
    }
    
    public static function findOrFail($id)
    {
        $record = self::find($id);
        if (!$record) {
             http_response_code(404);
             echo "404 Not Found";
             exit();
        }
        return $record;
    }

    public static function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $stmt = self::getDb()->prepare("SELECT * FROM `" . static::$table . "` WHERE `{$column}` {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public static function firstWhere($column, $operator, $value = null)
    {
        $results = self::where($column, $operator, $value);
        return $results[0] ?? null;
    }

    public static function create($data)
    {
        $keys = array_keys($data);
        $fields = implode(', ', array_map(fn($k) => "`$k`", $keys));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO `" . static::$table . "` ($fields) VALUES ($placeholders)";
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return self::find(self::getDb()->lastInsertId());
    }

    public function update($data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $this->$key = $value; // Update object property
        }
        $fieldsStr = implode(', ', $fields);
        
        $pk = static::$primaryKey;
        $sql = "UPDATE `" . static::$table . "` SET $fieldsStr WHERE `$pk` = ?";
        $values = array_values($data);
        $values[] = $this->$pk;
        
        $stmt = self::getDb()->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete()
    {
        $pk = static::$primaryKey;
        $sql = "DELETE FROM `" . static::$table . "` WHERE `$pk` = ?";
        $stmt = self::getDb()->prepare($sql);
        return $stmt->execute([$this->$pk]);
    }
}
