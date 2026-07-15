<?php

namespace App\Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;

    public string $content;

    public string $actionUrl;

    public function __construct(string $title, string $content, string $actionUrl)
    {
        $this->title = $title;
        $this->content = $content;
        $this->actionUrl = $actionUrl;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->content)
            ->action('View Institute Detail', $this->actionUrl)
            ->line('Thank you for using EduBase!');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }
}
