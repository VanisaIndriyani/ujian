<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'student_id',
        'recorded_by',
        'attendance_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

