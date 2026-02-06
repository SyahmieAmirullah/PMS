<?php

namespace App\Models;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $table = 'meeting';
    // Use default primary key 'id'

    protected $fillable = [
        'ProjectID',
        'MeetingTITLE',
        'MeetingOBJECTIVE',
        'MeetingDATE',
        'MeetingTIME',
    ];

    protected $casts = [
        'MeetingDATE' => 'date',
        'MeetingTIME' => 'datetime:H:i',
    ];

    /**
     * Relationships
     */
    
    // Meeting belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'id');
    }

    // Meeting has many attendance records
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'MeetingID', 'MeetingID');
    }

    /**
     * Scopes
     */
    
    public function scopeUpcoming($query)
    {
        return $query->where('MeetingDATE', '>=', now()->toDateString())
                     ->orderBy('MeetingDATE')
                     ->orderBy('MeetingTIME');
    }

    public function scopePast($query)
    {
        return $query->where('MeetingDATE', '<', now()->toDateString())
                     ->orderBy('MeetingDATE', 'desc')
                     ->orderBy('MeetingTIME', 'desc');
    }
}