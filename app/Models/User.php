<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function semesters(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Semester::class);
    }

    public function cgpa(): ?float {
        $courses = $this->semesters()
            ->with('courses')
            ->orderByDesc('semester_number')
            ->get()
            ->flatMap(fn (Semester $semester) => $semester->courses)
            ->unique('code');

        $totalCredits = (int) $courses->sum('credit_hours');

        if ($totalCredits === 0) {
            return null;
        }

        $totalPoints = $courses->sum(
            fn (Course $course) =>
                (int) $course->credit_hours * (float) $course->grade_point
        );

        return $totalPoints / $totalCredits;
    }
}
