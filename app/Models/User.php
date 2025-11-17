<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip',
        'nisn',
        'role',
        'classroom',
        'photo_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor: profile photo URL with graceful fallbacks.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        // If model has a photo_path attribute and it's set, return storage URL
        $path = $this->getAttribute('photo_path');
        if ($path) {
            try {
                // Use relative URL to avoid APP_URL/port mismatch in dev
                return '/storage/' . ltrim($path, '/');
            } catch (\Throwable $e) {
                // fall through to other strategies
            }
        }

        // Try Gravatar for users with email
        if ($this->email) {
            $hash = md5(strtolower(trim($this->email)));
            return "https://www.gravatar.com/avatar/{$hash}?s=160&d=identicon";
        }

        // Fallback to UI Avatars based on name
        $name = $this->name ?: 'User';
        $encodedName = urlencode($name);
        return "https://ui-avatars.com/api/?name={$encodedName}&background=059669&color=fff&size=160";
    }

    /**
     * Guru subjects relationship.
     */
    public function subjectsTeaching()
    {
        return $this->hasMany(Subject::class, 'guru_id');
    }

    /**
     * Subjects the murid enrolled in.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'student_id');
    }

    public function semesterGrades()
    {
        return $this->hasMany(SemesterGrade::class, 'student_id');
    }
}
