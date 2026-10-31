<?php

namespace App\Logs;

use Opcodes\LogViewer\Logs\Log;
use Illuminate\Support\Carbon;
use Opcodes\LogViewer\LogLevels\LaravelLogLevel;

class PinoLog extends Log
{
    public static string $name = 'Pino';
    
    // Regex to match the whole JSON line
    public static string $regex = '/^(?P<json>\{.*\})$/';

    protected array $pinoLevels = [
        10 => 'trace',
        20 => 'debug',
        30 => 'info',
        40 => 'warning',
        50 => 'error',
        60 => 'emergency',
    ];

    public function fillMatches(array $matches = []): void
    {
        $data = json_decode($matches['json'], true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['time'])) {
                // Pino usually logs in milliseconds (13 digits)
                $timestamp = (int) $data['time'];
                $this->datetime = $timestamp > 10000000000 ? Carbon::createFromTimestampMs($timestamp) : Carbon::createFromTimestamp($timestamp);
            } else {
                $this->datetime = now();
            }
            
            $pinoLevel = $data['level'] ?? 30;
            $this->level = $this->pinoLevels[$pinoLevel] ?? 'info';
            
            $this->message = $data['msg'] ?? '';
            
            // Collect metadata
            $this->context = array_diff_key($data, array_flip(['time', 'level', 'msg']));
        } else {
            parent::fillMatches($matches);
        }
    }
}
