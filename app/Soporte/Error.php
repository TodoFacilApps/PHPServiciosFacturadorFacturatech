<?php

namespace App\Soporte;

use Storage;


class Error
{
    public static function guardar($nombreLog , \Throwable $th, $tlhistorial=false){
        
        if($tlhistorial){//es historial, no lleva fecha sino nombre del archivo directo
            //verificar si existe log creado

        }
        $fileName = date('dmY')."_".date('His')."_".$nombreLog.".txt";

        $cadena = "";

        $getMessage = "<Message>".PHP_EOL."\t".$th->getMessage().PHP_EOL."<\Message>".PHP_EOL;
        $getCode = "<Code>".PHP_EOL."\t".$th->getCode().PHP_EOL."<\Code>".PHP_EOL;
        $getFile = "<File >".PHP_EOL."\t".$th->getFile().PHP_EOL."<\File>".PHP_EOL;
        $getLine = "<Line >".PHP_EOL."\t".$th->getLine ().PHP_EOL."<\Line>".PHP_EOL;

        $stringTrace = $th->getTraceAsString();
        $arrayTrace = explode("#" , $stringTrace);
        $n = count($arrayTrace);
        $trace = "";
        for ($i=0; $i < $n; $i++) { 
            $trace = $trace.$arrayTrace[$i].PHP_EOL;            
        }
        $getTrace = "<Stack Trace>".$trace."<\Stack Trace>";
        

        //$cadena = $th->__toString();
        $cadena = $getMessage.PHP_EOL.$getCode.PHP_EOL.$getFile.PHP_EOL.$getLine.PHP_EOL.$getTrace;

        if($tlhistorial){
            Storage::disk('errores')->prepend($fileName, $cadena);
        }else{
            Storage::disk('errores')->put($fileName , $cadena);
        }
    }

    public static function guardarLog($tlhistorial,$tcNombre, ...$tcMensaje){
        $tlhistorial=true;
        try {
            if($tcMensaje === null){
                $tcMensaje="tcMensaje es NULL";
            }
            $fileName="";
            if($tlhistorial){
                $fileName=$tcNombre;
            }else{
                $fileName = date('dmY')."_".date('His')."_".$tcNombre.".txt";
            }
            $tcFinal="";
            foreach ($tcMensaje as $n) {
                if(is_string($n)){
                    $tcFinal = $tcFinal . $n."\n\n";
                }else{
                    $tcFinal = $tcFinal . json_encode($n,JSON_PRETTY_PRINT)."\n\n";
                }
            }
            //var_dump($tcFinal);
            if($tlhistorial){
                $cabecera=date('Y-m-d H:i:s:u :::::::::::::::::::::::::::::::::::::')."$tcNombre \n";
                $tcFinal=$cabecera.$tcFinal."\n\n";
                Storage::disk('LogNormal')->prepend($fileName , "\xEF\xBB\xBF".$tcFinal);
            }else{
                Storage::disk('LogNormal')->put($fileName , "\xEF\xBB\xBF".$tcFinal);
            }
        } catch (\Throwable $th) {
            Error::guardar("guardarLog", $th);
        }
    }

    public static function guardarPrueba($tlhistorial,$tcNombre, ...$tcMensaje){
        $tlhistorial=true;
        try {
            if($tcMensaje === null){
                $tcMensaje="tcMensaje es NULL";
            }
            $fileName="";
            if($tlhistorial){
                $fileName=$tcNombre;
            }else{
                $fileName = date('dmY')."_".date('His')."_".$tcNombre.".txt";
            }
            $tcFinal="";
            foreach ($tcMensaje as $n) {
                if(is_string($n)){
                    $tcFinal = $tcFinal . $n."\n\n";
                }else{
                    $tcFinal = $tcFinal . json_encode($n,JSON_PRETTY_PRINT)."\n\n";
                }
            }
            //var_dump($tcFinal);
            if($tlhistorial){
                $cabecera=date('Y-m-d H:i:s:u :::::::::::::::::::::::::::::::::::::')."$tcNombre \n";
                $tcFinal=$cabecera.$tcFinal."\n\n";
                Storage::disk('LogPrueba')->prepend($fileName , "\xEF\xBB\xBF".$tcFinal);
            }else{
                Storage::disk('LogPrueba')->put($fileName , "\xEF\xBB\xBF".$tcFinal);
            }

        } catch (\Throwable $th) {
            Error::guardar("GuardarPrueba",$th);
        }
    }


    public static function guardarLog2($tnCliente, $tnEmpresa, $tcNombre, $tcMensaje){

        $oResult=DB::table("BITACORA")
                    ->insert();

        if($tcMensaje === null){
            $tcMensaje="tcMensaje es NULL";
        }
        $fileName = date('dmY')."_".date('His')."_".$tcNombre.".txt";
        Storage::disk('LogNormal')->put($fileName , $tcMensaje);
    }
    
    
    public static function LogFacturaEmpresa($tcNombre, $tcMensaje){
        if($tcMensaje === null){
            $tcMensaje="Mensaje es NULL";
        }
        $fileName = date('dmY')."_".date('His')."_".$tcNombre.".txt";
        Storage::disk('LogFacturaEmpresa')->put($fileName , $tcMensaje);
    }

}

?>