<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
// Necesitaremos el modelo Comentario para ciertas tareas.
use App\Comentario;

class ComentarioController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return response()->json(['status'=>'ok','data'=>Comentario::all()], 200);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//return "Se muestra Comentario con id: $id";
		$Comentario=Comentario::find($id);
 
		// Si no existe ese Comentario devolvemos un error.
		if (!$Comentario)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Comentario con ese código.'])],404);
		}
 
		return response()->json(['status'=>'ok','data'=>$Comentario],200);
	}
}
