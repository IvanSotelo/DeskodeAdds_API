<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
// Necesita los dos modelos Video y Venta
use App\Video;
use App\Venta;

class VentaVideoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($idVenta)
	{
		// Devolverá todos los videos.
		//return "Mostrando los videos de la Venta con Id $idVenta";
		$Venta=Venta::find($idVenta);
 
		if (! $Venta)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Venta con ese código.'])],404);
		}
 
		return response()->json(['status'=>'ok','data'=>$Venta->videos()->get()],200);
		//return response()->json(['status'=>'ok','data'=>$Venta->aviones],200);
	}
 
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($idVenta,$idVideo)
	{
		//
		return "Se muestra Video $idVideo de la Venta $idVenta";
	}
}
