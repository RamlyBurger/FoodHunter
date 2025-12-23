<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmailChangeOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $newEmail;

    public function __construct(User $user, string $otp, string $newEmail)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->newEmail = $newEmail;
    }

    public function build()
    {
        return $this->subject('Email Change Verification - FoodHunter')
                    ->view('emails.email-change-otp');
    }
}
