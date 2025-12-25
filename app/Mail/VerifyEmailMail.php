<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $code;
    public string $type; // 'verification' or 'reset'

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $code, string $type = 'verification')
    {
        $this->user = $user;
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $subject = $this->type === 'reset' 
            ? 'Password Reset Code - Zhengzhou University'
            : 'Email Verification Code - Zhengzhou University';

        return $this->subject($subject)
            ->view('emails.verify')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
                'type' => $this->type,
            ]);
    }
}
