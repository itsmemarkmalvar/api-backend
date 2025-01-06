<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        // Create a mobile deep link
        $mobileUrl = "binibaby://reset-password?" . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset()
        ]);

        // Create a web fallback URL
        $webUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset()
        ], false));

        return (new MailMessage)
            ->subject('Reset Your BiniBaby Password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->line('If you\'re on your mobile device, click here to reset your password in the BiniBaby app:')
            ->action('Reset Password in App', $mobileUrl)
            ->line('Or if you\'re on a computer, click here:')
            ->action('Reset Password on Web', $webUrl)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
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