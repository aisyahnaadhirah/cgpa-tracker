<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogCourseCreated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CourseCreated $event): void
    {
        logger()->info('Kursus baharu berjaya dicipta', [
            'course_id' => $event->course->id,
            'course_code' => $event->course->code,
            'semester_id' => $event->course->semester_id,
        ]);
    }
}
