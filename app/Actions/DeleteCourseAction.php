<?php

namespace App\Actions;

use App\Models\Course;
use Illuminate\Support\Facades\Cache;

class DeleteCourseAction
{
    /**
     * Create a new class instance.
     */
    public function handle(Course $course): void
    {
        $userId = $course->semester->user_id;
        $course->delete();
        Cache::forget('cgpa_'.$userId);
    }
}
