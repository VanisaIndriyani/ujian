<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'guru_id',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}

