<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Semester extends Model
{
    protected $fillable = [
        'name',
        'academic_year',
        'semester_number',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasmany(Course::class);
    }

    public function gpa(): ?float {
        $totalCredits = (int) $this->courses->sum('credit_hours');

        if ($totalCredits === 0) {
            return null;
        }

        $totalPoints = $this->courses->sum(
            fn (Course $course) =>
                (int) $course->credit_hours * (float) $course->grade_point
        );

        return $totalPoints / $totalCredits;
    }  //nak kira gpa
}
