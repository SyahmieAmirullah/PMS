<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    use HasFactory;

    protected $table = 'phase';
    protected $primaryKey = 'PhaseID';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'ProjectID',
        'PhaseNAME',
        'PhaseDESC',
        'PhaseUPDATE',
        'PhaseORDER',
    ];

    protected $casts = [
        'PhaseORDER' => 'integer',
    ];

    /**
     * Relationships
     */
    
    // Phase belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'ProjectID', 'ProjectID');
    }

    // Phase has many documents
    public function documents()
    {
        return $this->hasMany(Document::class, 'PhaseID', 'PhaseID');
    }
}