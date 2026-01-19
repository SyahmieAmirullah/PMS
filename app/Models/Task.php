<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'task';
    protected $primarykey= 'TaskID';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'TaskNAME',
        'TaskDESC',
        'TaskDUE',
        'ProjectID',
    ];

    protected $casts = [
        'TaskDUE' => 'date',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID','ProjectID');
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