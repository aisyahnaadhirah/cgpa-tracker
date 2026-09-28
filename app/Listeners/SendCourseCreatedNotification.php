<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use App\Notifications\CourseCreatedNotification;

class SendCourseCreatedNotification
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
        $course = $event->course; //amik kursus yang saveCourse() hantar dari livewire 
        $user = $course->semester->user; //amik course dari semester mana, dan semester tu dari user yang mana

        $user->notify(new CourseCreatedNotification(
            $course->code,
            $course->name,
        ));
    }
}
