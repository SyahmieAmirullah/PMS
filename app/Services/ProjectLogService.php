<?php

namespace App\Services;

use App\Models\ProjectLog;

class ProjectLogService
{
    public static function log(
        int $projectId,
        string $title,
        string $type = 'update',
        ?string $desc = null,
        ?int $staffId = null
    ): ProjectLog {
        return ProjectLog::create([
            'ProjectID' => $projectId,
            'LogTITLE' => $title,
            'LogTYPE' => $type,
            'LogDESC' => $desc,
            'LogDATE' => now(),
            'StaffID' => $staffId,
            'ProjectDATE' => now()->toDateString(),
            'ProjectTIME' => now()->format('H:i:s'),
        ]);
    }
}
