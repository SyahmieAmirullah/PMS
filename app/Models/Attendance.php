<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';
    // Use default primary key 'id'

    protected $fillable = [
        'MeetingID',
        'StaffID',
        'AttandanceSTATUS',
        'AttandanceDATE',
        'AttandanceTIME',
        'AbsentREASON',
        'AttandanceLOCATION',
        'AttandanceLAT',
        'AttandanceLNG',
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
        return $this->belongsTo(Meeting::class, 'MeetingID', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'id');
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
