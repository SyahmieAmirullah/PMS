<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';
    // Use default primary key 'id'
    protected $fillable = [
        'StaffNAME',
        'StaffPHONE',
        'StaffEMAIL',
        'StaffPASSWORD',
    ];

    protected $hidden = [
        'StaffPASSWORD',
    ];

    protected $rememberTokenName = 'remember_token';

    public function getAuthPassword()
    {
        return $this->StaffPASSWORD;
    }

    public function roles()
    {
        return $this->hasMany(StaffRole::class, 'StaffID', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'staff_project', 'StaffID', 'ProjectID')
            ->withPivot('ProjectSTART', 'ProjectDUE')
            ->withTimestamps();
    }

    public function staffProjects()
    {
        return $this->hasMany(StaffProject::class, 'StaffID');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'StaffID', 'id');
    }

    public function hasRole(string $roleType): bool
    {
        return $this->roles()->where('RoleTYPE', $roleType)->exists();
    }

    public function roleTypes()
    {
        return $this->roles()->pluck('RoleTYPE');
    }
    
    // Remove attendances relationship - it doesn't exist in the ERD
}
