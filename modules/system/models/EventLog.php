<?php namespace System\Models;

use Model;

/**
 * Model for logging system errors and debug trace messages
 */
class EventLog extends Model
{

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'system_event_logs';

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['details'];

    /**
     * Creates a log record
     * @param string $message Specifies the message text
     * @param string $level Specifies the logging level
     * @param string $details Specifies the error details string
     * @return self
     */
    public static function add($message, $level = 'info', $details = null)
    {
        $record = new static;
        $record->message = $message;
        $record->level = $level;

        if ($details !== null)
            $record->details = (array) $details;

        $record->save();

        return $record;
    }

    /**
     * Beautify level value.
     * @param  string $level
     * @return string
     */
    public function getLevelAttribute($level)
    {
        return ucfirst($level);
    }

}