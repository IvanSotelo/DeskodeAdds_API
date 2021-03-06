<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
// Necesitaremos el modelo Pago para ciertas tareas.
use App\Pago;

// Activamos uso de caché.
use Illuminate\Support\Facades\Cache;

class PagoController extends Controller
{

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
	    // Activamos la caché de los resultados.
        //  Cache::remember('tabla', $minutes, function()
        $pagos=Cache::remember('pagos',20/60, function()
        {
            // Caché válida durante 20 segundos.
            return Pago::all();
        });
        // Con caché.
        return response()->json(['Pagos'=>$pagos], 200);
		//return response()->json(['status'=>'ok','data'=>Pago::all()], 200);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//return "Se muestra Pago con id: $id";
		$Pago=Pago::find($id);
 
		// Si no existe ese Pago devolvemos un error.
		if (!$Pago)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Pago con ese código.'])],404);
		}

		return response()->json(['Pago'=>$Pago],200);
	}
}
