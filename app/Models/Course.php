<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'credit_hours',
        'grade_point',
    ];

    public function semester(): BelongsTo {
        return $this->belongsTo(Semester::class);
    }
}
