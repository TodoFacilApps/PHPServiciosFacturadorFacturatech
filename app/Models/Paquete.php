<?php

namespace App\Models;

class Paquete {

    public $error;
    public $status;
    public $message="";
    public $messageMostrar=0;
    public $messageSistema="";
    public $values;

    function __construct2()
    {
        $this->error = 0;
        $this->status = 1;
        $this->message = "Exito";
    }

    function __construct($tnError, $tnEstado, $tcMensaje, $tcValues)
    {
        $this->error = $tnError;
        $this->status = $tnEstado;
        $this->message = $tcMensaje;
        $this->values = $tcValues;//tcValues;
    }

}
