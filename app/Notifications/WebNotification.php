<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $timing;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $timing = null)
    {
        $this->title = $title;
        $this->timing = $timing;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'timing' => $this->timing,
        ];
    }
}
