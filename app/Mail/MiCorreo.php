<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MiCorreo extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre; // Variable para el nombre del usuario
    public $mensaje; // Variable para el mensaje personalizado

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre, $mensaje)
    {
        $this->nombre = $nombre;
        $this->mensaje = $mensaje;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('welcome');
    }
}
