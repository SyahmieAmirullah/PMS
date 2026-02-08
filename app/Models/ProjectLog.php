<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    use HasFactory;

    protected $table = 'project_logs';
    // Use default primary key 'id'

    protected $fillable = [
        'ProjectID',
        'ProjectTIME',
        'ProjectDATE',
        'PhaseBEFORE',
        'PhaseCURRENT',
        'ChangeREASON',
        'LogTITLE',
        'LogDESC',
        'LogTYPE',
        'LogDATE',
        'StaffID',
        'LogATTACHMENTS',
    ];

    protected $casts = [
        'ProjectDATE' => 'date',
        'ProjectTIME' => 'datetime:H:i',
        'LogDATE' => 'datetime',
        'LogATTACHMENTS' => 'array',
    ];

    /**
     * Relationships
     */
    
    // Log belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'id');
    }

    /**
     * Scopes
     */
    
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('ProjectDATE', '>=', now()->subDays($days))
                     ->orderBy('ProjectDATE', 'desc')
                     ->orderBy('ProjectTIME', 'desc');
    }
}
