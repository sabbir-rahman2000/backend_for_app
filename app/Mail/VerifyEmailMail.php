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

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your Verification Code')
            ->view('emails.verify')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
            ]);
    }
}
