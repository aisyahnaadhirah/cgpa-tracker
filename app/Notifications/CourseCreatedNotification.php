<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $courseCode,
        public string $courseName,
    ){
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kursus baharu berjaya ditambah')
            ->greeting('Hai ' . $notifiable->name . '!')
            ->line('Kursus berikut telah ditambah ke CGPA Tracker anda:')
            ->line('Kod kursus: ' . $this->courseCode)
            ->line('Nama kursus: ' . $this->courseName)
            ->action('Lihat CGPA Tracker', route('dashboard'))
            ->line('Terima kasih kerana menggunakan CGPA Tracker!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
