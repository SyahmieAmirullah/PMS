<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    use HasFactory;

    protected $table = 'staff_role';
    // Use default primary key 'id'

    protected $fillable = [
        'StaffID',
        'RoleDESC',
        'RoleTYPE',
        'RolePRO',
    ];

    /**
     * Relationships
     */
    
    // Role belongs to a staff member
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'id');
    }
}