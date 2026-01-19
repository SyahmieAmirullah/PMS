<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';
    protected $primaryKey = 'FeedbackID';

    protected $fillable = [
        'ProjectID',
        'FeedbackTITLE',
        'FeedbackDESC',
        'FeedbackTIME',
    ];

    protected $casts = [
        'FeedbackTIME' => 'datetime',
    ];

    /**
     * Relationships
     */
    
    // Feedback belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'ProjectID');
    }

    /**
     * Scopes
     */
    
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('FeedbackTIME', '>=', now()->subDays($days))
                     ->orderBy('FeedbackTIME', 'desc');
    }
}