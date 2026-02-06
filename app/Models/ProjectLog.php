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
    ];

    protected $casts = [
        'ProjectDATE' => 'date',
        'ProjectTIME' => 'datetime:H:i',
    ];

    /**
     * Relationships
     */
    
    // Log belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'id');
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