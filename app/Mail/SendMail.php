<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return
     */
    public function build(): SendMail
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Welcome to Our Platform')
            ->with('user', $this->customer)
            ->view('mail_template.contact_mail_success');
    }
}
