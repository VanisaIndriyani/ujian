<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'creator_id',
        'classroom',
        'type',
        'title',
        'description',
        'question_url',
        'material_path',
        'questions_json',
        'answer_key_json',
        'start_at',
        'end_at',
        'semester',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'questions_json' => 'array',
        'answer_key_json' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}

