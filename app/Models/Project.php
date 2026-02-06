<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'project';
    // Use default primary key 'id'
    protected $fillable = [
        'ProjectNAME',
        'ProjectDESC',
        'ProjectSTATUS',
        'ClientNAME',
        'ClientPHONE',
        'ClientEMAIL',
    ];

    public $incrementing = true;

    protected $casts = [
        'ProjectSTATUS' => 'string',
    ];

    // Relationships
    public function phases()
    {
        return $this->hasMany(Phase::class, 'ProjectID', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'ProjectID', 'id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'ProjectID', 'id');
    }

    public function projectLogs()
    {
        return $this->hasMany(ProjectLog::class, 'ProjectID');
    }

    public function staffProjects()
    {
        return $this->hasMany(StaffProject::class, 'ProjectID');
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_project', 'ProjectID', 'StaffID')
            ->withPivot('ProjectSTART', 'ProjectDUE')
            ->withTimestamps();
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('ProjectSTATUS', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('ProjectSTATUS', 'completed');
    }

    public function scopeOnHold($query)
    {
        return $query->where('ProjectSTATUS', 'on_hold');
    }
}
