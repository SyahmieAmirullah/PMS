<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';
    protected $primaryKey = 'AttandanceID';

    protected $fillable = [
        'MeetingID',
        'AttandanceSTATUS',
        'AttandanceDATE',
        'AttandanceTIME',
        'AbsentREASON',
        'AttandanceLOCATION',
    ];

    protected $casts = [
        'AttandanceDATE' => 'date',
        'AttandanceTIME' => 'datetime:H:i',
    ];

    /**
     * Relationships
     */
    
    // Attendance belongs to a meeting
    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'MeetingID', 'MeetingID');
    }

    /**
     * Scopes
     */
    
    public function scopePresent($query)
    {
        return $query->where('AttandanceSTATUS', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('AttandanceSTATUS', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('AttandanceSTATUS', 'late');
    }
}