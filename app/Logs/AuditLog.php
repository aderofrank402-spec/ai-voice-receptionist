<?php

namespace App\Logs;

use Opcodes\LogViewer\Logs\Log;

class AuditLog extends Log
{
    public static string $name = 'Platform Audit';

    // Matches standard Laravel [datetime] environment.level: message
    public static string $regex = '/^\[(?<datetime>.*)\] (?<env>\w+)\.(?<level>\w+): (?<message>.*)/';

    public function fillMatches(array $matches = []): void
    {
        parent::fillMatches($matches);

        // We can add custom logic here if we add more metadata to audit logs
    }
}
