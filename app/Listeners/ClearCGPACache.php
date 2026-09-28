<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ClearCGPACache
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
        $userId = $event->course->semester->user_id;

        Cache::forget('cgpa_' . $userId);
    }
}
