<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogCourseCreatedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $courseId,
        public string $courseCode,
        public int $semesterId,
    ){
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger()->info('Kursus baharu berjaya dicipta', [
            'course_id' => $this->courseId,
            'course_code' => $this->courseCode,
            'semester_id' => $this->semesterId,
        ]);
    }
}
