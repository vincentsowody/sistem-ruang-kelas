<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordByAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Akun Anda — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password-by-admin',
            with: [
                'user'     => $this->user,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
