<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'score',
        'semester',
        'notes',
        'recorded_by',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

