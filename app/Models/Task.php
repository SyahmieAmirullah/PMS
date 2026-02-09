<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'task';
    // Use default primary key 'id'
    protected $fillable = [
        'TaskNAME',
        'TaskDESC',
        'TaskDUE',
        'ProjectID',
        'StaffID',
        'TaskSTATUS',
    ];

    protected $casts = [
        'TaskDUE' => 'date',
        'TaskSTATUS' => 'string',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'id');
    }

    // Scopes for filtering
    public function scopeUpcoming($query)
    {
        return $query->where('TaskDUE', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('TaskDUE', '<', now());
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('ProjectID', $projectId);
    }
}
