<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('VENTAFACTURA', function (Blueprint $table) {
            $table->integer('Venta');
            $table->integer('NumeroFactura')->length(10);   //nro
            $table->string('NitEmison', 20);// nit Empresa
            $table->date('FechaEmision');// fecha decha la orden de factura (tiene que reflejar la factura)
            $table->string('HoraEmision', 12); // hora de la orden de facura (tiene que reflejar la factura)
            $table->integer('ValidoSin')->length(1)->default(1);//valor por defecto 1 = pendiente, si el emitir escorecto 2
            $table->integer('Moneda')->length(1);  //codigo de moneda 1= bolivianos 2 = dolares
            $table->integer('CodigoSucursal')->length(10); //codigo sucursal habilitado por inpuesto
            $table->integer('CodigoPuntoVenta')->length(10);// Codigo punto de venta habilitado por impuestos
            $table->integer('TipoDocumentoSector')->length(10);       //dependiendo del formulario (canbiar al tipo de formulario para que fucione con tipodocumento sector)
            $table->string('CodigoCliente', 100);          //Codigo del cliente registrado por la empresa
            $table->integer('DocumentoIdentidad')->length(2);     //Tipo de documento del Cliente
            $table->string('NumeroDocumento', 20);               // Numero de documento del Cliente
            $table->string('Complemento', 5)->nullable();            // Complemento del documento del cliente
            $table->integer('Codexcepci')->length(1);       // por default 0 pero si el documeto es nit => consultar a impuesto si es valido colocar  0 y si es invalido colocar 1
            $table->string('RazonSocial', 200);          // Nombre o Razon Social del Cliente
            $table->string('Email', 254);               //Email del Cliente
            $table->integer('MetodoPago')->length(3);   //Metodo que pago de la nota de Venta
            $table->string('NumeroTarjeta')->nullable();            //si el metodo de pago es 2=Tarjeta  anotar los primeros y ultimos 4 nro
            $table->integer('Cufd')->length(10)->nullable();        //nulo por default asta verificar en ...
            $table->string('Cuf', 100)->nullable();                 //nulo por default asta verificar en ...
            $table->string('Cafc', 100);//Código de Autorización de Facturación y Cobro //codigo pedir por formulario con opcion de ser generado
            $table->integer('Leyenda')->length(10)->nullable();     //nulo por defecto en espera de confirmacion de facturatech
            $table->integer('Nota')->length(10)->nullable();            //
            $table->integer('GiftCard')->length(10);        //
            $table->date('FechaCreacion');                     // fecha actual de envio de orden de factura
            $table->string('HoraCreacion', 12);                // hora actual de envio de orden de factura
            $table->integer('EstadoSiat')->length(4)->default(1);       // inicia como 1 = pendiente
            $table->text('Observacion')->nullable();                //no tengo ni idea
            $table->integer('TipoEmision')->length(1)->nullable();      //
            $table->integer('Evento')->length(2)->nullable();
            $table->integer('NitEspecial')->length(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VENTAFACTURA');
    }
};
