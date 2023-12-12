<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $subject;
    public $message;
    public $variable3;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$subject,$message)
    {
        $this->name = $name;
        $this->subject = $subject;
        $this->message = $message;
        $this->variable3 = "cdkondaor";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return view('emailPassword', [
                'name' => (string) $this->name,
                'message' => (string) $this->message,
                'variable3' => (string) $this->variable3,
            ]);

        return $this->from(env('MAIL_USERNAME'), env('APP_NAME'))
            ->subject($this->subject)
            ->view('emailPassword', [
                'name' => (string) $this->name,
                'message' => (string) $this->message,
                'variable3' => (string) $this->variable3,
            ]);
    }


}
