<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use App\Jobs\LogCourseCreatedJob;


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
        LogCourseCreatedJob::dispatch(
            (int) $event->course->id,
            $event->course->code,
            (int) $event->course->semester_id,
        );
    }
}
