<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modelos\mPaquetePagoFacil;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Sucursal;
use App\Http\Controllers\UsuarioEmpresaController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function dashboard()
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $empresasController = new UsuarioEmpresaController();
        $oEmpresas = $empresasController->misEmpresasReturn();
        //segundo caso ventas por sucursal por tres meses
        $fechaActual = Carbon::now();
        $fechaTresMesesAntes = $fechaActual->copy();
        $fechaTresMesesAntes = $fechaTresMesesAntes->subMonths(3);

        $fechaActual = $fechaActual->format('Y-m-d');
        $fechaTresMesesAntes = $fechaTresMesesAntes->format('Y-m-d');

        //(pastel)ventas por sucursal
        $loVentaSucursal = Venta::select('EMPRESASUCURSAL.Direccion as Sucursal' , \DB::raw('COUNT("VENTA"."Venta") as "Cantidad"'))
            ->leftJoin('EMPRESASUCURSAL', 'EMPRESASUCURSAL.Sucursal', '=', 'VENTA.Sucursal')
            ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)
            ->whereBetween('VENTA.Fecha', [$fechaTresMesesAntes, $fechaActual])
            ->groupBy('EMPRESASUCURSAL.Direccion')
            ->get();


        $ldPrimeroMes = Carbon::now()->firstOfMonth();
        $loVentaConFacturaPendiente;
        $loVentaConFacturaValidas;
        $loVentaConFacturaAnuladas;
        $loVentaConFacturaObservadas;
        $loVentaConFacturaRechazadas;

        $anEstadosSiat = [1,2,3,4,5];
        foreach ($anEstadosSiat as $item) {
            $consulta = Venta::select('VENTA.Fecha' , \DB::raw('COUNT("VENTA"."Venta") as "Cantidad"'))
            ->leftJoin('EMPRESASUCURSAL', 'EMPRESASUCURSAL.Sucursal', '=', 'VENTA.Sucursal')
            ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)

            ->whereBetween('VENTA.Fecha', [$ldPrimeroMes, $fechaActual])
            ->whereIn('VENTA.Venta', function($query) use ($item) {
                $query->select('Venta')
                ->from('VENTAFACTURA')
                ->where('EstadoSiat', $item);
                })
            ->groupBy('VENTA.Fecha')
            ->get();

            switch($item){
                case 1:
                    $loVentaConFacturaPendiente =$consulta;
                    break;
                case 2:
                    $loVentaConFacturaValidas =$consulta;
                    break;
                case 3:
                    $loVentaConFacturaAnuladas =$consulta;
                    break;
                case 4:
                    $loVentaConFacturaObservadas =$consulta;
                    break;
                case 5:
                    $loVentaConFacturaRechazadas =$consulta;
                    break;
            }
        }

        $loVentaConFactura = Venta::select('VENTA.Fecha' , \DB::raw('COUNT("VENTA"."Venta") as "Cantidad"'))
            ->leftJoin('EMPRESASUCURSAL', 'EMPRESASUCURSAL.Sucursal', '=', 'VENTA.Sucursal')
            ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)
            ->whereBetween('VENTA.Fecha', [$ldPrimeroMes, $fechaActual])
            ->whereIn('VENTA.Venta', function($query) {
                $query->select('Venta')->from('VENTAFACTURA');
                })
            ->groupBy('VENTA.Fecha')
            ->get();

        $loVentaSinFactura = Venta::select('VENTA.Fecha' , \DB::raw('COUNT("VENTA"."Venta") as "Cantidad"'))
            ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)
            ->whereBetween('VENTA.Fecha', [$ldPrimeroMes, $fechaActual])
            ->whereNotIn('VENTA.Venta', function($query) {
                $query->select('Venta')->from('VENTAFACTURA');
                })
            ->groupBy('VENTA.Fecha')
            ->get();


        // Obtener datos de evolucion de las ventas Totales
        $loEvolucionVentas = Venta::select('Fecha', 'TotalPagar as Total')
        ->orderBy('Fecha')
        ->get();


        $oPaquete->error = 0; // Error Generico
        $oPaquete->status = 1; // Sucedio un error
        $oPaquete->messageSistema = "comando ejecutado";
        $oPaquete->message = "ejecusion sin inconvenientes";
        $oPaquete->values = [
            $oEmpresas,
            $loVentaSucursal,
            $loEvolucionVentas,
            $loVentaSinFactura,
            $loVentaConFacturaPendiente,
            $loVentaConFacturaValidas,
            $loVentaConFacturaAnuladas,
            $loVentaConFacturaObservadas,
            $loVentaConFacturaRechazadas,
            $loVentaConFactura];
        //notas de ventas realizadad por tres meces
        return response()->json($oPaquete);
    }

    public function dashboardEmpresa(Request $request)
    {
        $oPaquete = new mPaquetePagoFacil(0, 1, "Error inesperado.. inicio ", null);

        $empresasController = new UsuarioEmpresaController();
        $oEmpresa = $empresasController->esMiEmpresa($request->tnEmpresa);
        // primer caso
        if($oEmpresa){
            $loProducto = Producto::select(\DB::raw('COUNT(*) as CantidadProducto'))
            ->where('Estado', 1)
            ->where('Empresa', $oEmpresas[0]->Empresa)
            ->first();
            $cantidadProductos;
            //cantidad de productos registrados y sincronizados con siat
            if ($loProducto) {
                $cantidadProductos = $loProducto->cantidadproducto;
            } else {
                $cantidadProductos = 0; // Si no se encuentra ningún producto, establece la cantidad en 0
            }
            //segundo caso ventas por sucursal por tres meses
            $fechaActual = Carbon::now();
            $fechaTresMesesAntes = $fechaActual->copy();
            $fechaTresMesesAntes = $fechaTresMesesAntes->subMonths(3);

            $fechaActual = $fechaActual->format('Y-m-d');
            $fechaTresMesesAntes = $fechaTresMesesAntes->format('Y-m-d');

            //(pastel)ventas por sucursal
            $loVentaSucursal = Venta::select('EMPRESASUCURSAL.Direccion as Sucursal' , \DB::raw('COUNT("VENTA"."Venta") as "Cantidad"'))
                ->leftJoin('EMPRESASUCURSAL', 'EMPRESASUCURSAL.Sucursal', '=', 'VENTA.Sucursal')
                ->where('VENTA.Empresa', $oEmpresas[0]->Empresa)
                ->whereBetween('VENTA.Fecha', [$fechaTresMesesAntes, $fechaActual])
                ->groupBy('EMPRESASUCURSAL.Direccion')
                ->get();


            // Obtener datos de evolucion de las ventas Totales
            $loEvolucionVentas = Venta::select('Fecha', 'TotalPagar as Total')
            ->orderBy('Fecha')
            ->get();


            $oPaquete->error = 0; // Error Generico
            $oPaquete->status = 1; // Sucedio un error
            $oPaquete->messageSistema = "comando ejecutado";
            $oPaquete->message = "ejecusion sin inconvenientes";
            $oPaquete->values = [$oEmpresas, $cantidadProductos, $loVentaSucursal,$loEvolucionVentas];

        }else{
            $oPaquete->error = 1; // Error Generico
            $oPaquete->status = 0; // Sucedio un error
            $oPaquete->messageSistema = "a ocurrido un error";
            $oPaquete->message = "el Usuario no esta Vinculado con la empresa";
            $oPaquete->values = null;

        }
        //notas de ventas realizadad por tres meces
        return response()->json($oPaquete);
    }

}
