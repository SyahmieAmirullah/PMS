<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_project';

    protected $fillable = [
        'StaffID',
        'ProjectID',
        'ProjectSTART',
        'ProjectDUE',
    ];

    protected $casts = [
        'ProjectSTART' => 'date',
        'ProjectDUE' => 'date',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'id');
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->whereHas('project', function($q) {
            $q->where('ProjectSTATUS', 'in_progress');
        });
    }

    public function scopeOverdue($query)
    {
        return $query->where('ProjectDUE', '<', now())
            ->whereHas('project', function($q) {
                $q->whereNotIn('ProjectSTATUS', ['completed', 'cancelled']);
            });
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('StaffID', $staffId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('ProjectID', $projectId);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereBetween('ProjectSTART', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // Accessor for checking if assignment is overdue
    public function getIsOverdueAttribute()
    {
        if (!$this->ProjectDUE) return false;
        
        return $this->ProjectDUE->isPast() && 
               !in_array($this->project->ProjectSTATUS ?? '', ['completed', 'cancelled']);
    }

    // Accessor for duration in days
    public function getDurationDaysAttribute()
    {
        if (!$this->ProjectSTART || !$this->ProjectDUE) return 0;
        
        return $this->ProjectSTART->diffInDays($this->ProjectDUE);
    }

    // Accessor for remaining days
    public function getRemainingDaysAttribute()
    {
        if (!$this->ProjectDUE) return null;
        
        $remaining = now()->diffInDays($this->ProjectDUE, false);
        return $remaining < 0 ? 0 : $remaining;
    }

    // Accessor for status
    public function getStatusAttribute()
    {
        if ($this->is_overdue) {
            return 'Overdue';
        }
        
        if ($this->ProjectSTART && $this->ProjectSTART->isFuture()) {
            return 'Upcoming';
        }
        
        if ($this->project && $this->project->ProjectSTATUS === 'completed') {
            return 'Completed';
        }
        
        return 'In Progress';
    }
}
