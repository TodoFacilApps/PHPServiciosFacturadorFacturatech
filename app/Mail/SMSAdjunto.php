<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SMSAdjunto extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $subjet = 'Mensaje Recibido';
    public $msg;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->msg = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /*
        return $this->view('mensajeAdjunto')
        ->attachData($this->msg['archivo'], 'documento.pdf', [
            'mime' => 'application/pdf'
        ]);*/
        // Decodificar el contenido base64
        $pdfContent = base64_decode($this->msg['archivoPDF']);

        
              return $this->view('mensajeAdjunto') // Especifica el nombre de la vista
            ->subject($this->msg['subjet'])
            ->attachData($pdfContent, 'archivo.pdf', [
                'mime' => 'application/pdf',
            ]);
            
    }
}
