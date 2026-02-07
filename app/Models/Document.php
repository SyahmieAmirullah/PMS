<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $table = 'document';
    // Use default primary key 'id'

    protected $fillable = [
        'PhaseID',
        'DocumentNAME',
        'DocumentDATE',
        'DocumentVERSION',
        'DocumentPATH',
    ];

    protected $casts = [
        'DocumentDATE' => 'date',
    ];

    protected $appends = [
        'document_url',
    ];

    /**
     * Relationships
     */
    
    // Document belongs to a phase
    public function phase()
    {
        return $this->belongsTo(Phase::class, 'PhaseID', 'id');
    }

    /**
     * Accessors
     */
    
    // Get full URL for document
    public function getDocumentUrlAttribute()
    {
        return $this->DocumentPATH 
            ? Storage::url($this->DocumentPATH) 
            : null;
    }

    /**
     * Methods
     */
    
    public function deleteFile()
    {
        if ($this->DocumentPATH && Storage::exists($this->DocumentPATH)) {
            Storage::delete($this->DocumentPATH);
        }
    }
}
