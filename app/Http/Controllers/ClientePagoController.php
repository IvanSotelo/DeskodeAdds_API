<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// Necesita los dos modelos Cliente y Pago
use App\Cliente;
use App\Pago;

// Necesitamos la clase Response para crear la respuesta especial con la cabecera de localización en el método Store()
use Response;

// Activamos uso de caché.
use Illuminate\Support\Facades\Cache;

class ClientePagoController extends Controller
{
	// Configuramos en el constructor del controlador la autenticación usando el Middleware auth.basic,
	// pero solamente para los métodos de crear, actualizar y borrar.
	//public function __construct()
	//{
	//	$this->middleware('auth.basic',['only'=>['store','update','destroy']]);
	//}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($idCliente)
	{
		// Devolverá todos los pagos.
		//return "Mostrando los pagos del Cliente con Id $idCliente";
		$Cliente=Cliente::find($idCliente);

		if (!$Cliente)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Cliente con ese código.'])],404);
		}

 		// Activamos la caché de los resultados.
		// Como el closure necesita acceder a la variable $ fabricante tenemos que pasársela con use($fabricante)
		// Para acceder a los modelos no haría falta puesto que son accesibles a nivel global dentro de la clase.
		//  Cache::remember('tabla', $minutes, function()
		$Pagos=Cache::remember('clavePagos',2, function() use ($Cliente)
		{
			// Caché válida durante 2 minutos.
			return $Cliente->pagos()->get();
		});

		// Respuesta con caché:
		return response()->json(['Pagos'=>$Pagos],200);

		// Respuesta sin caché:
		//return response()->json(['status'=>'ok','data'=>$Cliente->pagos()->get()],200);
		//return response()->json(['status'=>'ok','data'=>$Cliente->pagos],200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request,$idCliente)
	{
        /* Necesitaremos el idCliente que lo recibimos en la ruta */

		// Primero comprobaremos si estamos recibiendo todos los campos.
		if ( !$request->input('Pago') || !$request->input('FechaPago') || !$request->input('ProxPago') || !$request->input('Estatus'))
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
			return response()->json(['errors'=>array(['code'=>422,'message'=>'Faltan datos necesarios para el proceso de alta.'])],422);
		}

		// Buscamos el Cliente.
		$Cliente= Cliente::find($idCliente);

		// Si no existe el Cliente que le hemos pasado mostramos otro código de error de no encontrado.
		if (!$Cliente)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Cliente con ese código.'])],404);
		}

		// Si el Cliente existe entonces lo almacenamos.
		// Insertamos una fila en Pagos con create pasándole todos los datos recibidos.
		$nuevoPago=$Cliente->pagos()->create($request->all());

		// Más información sobre respuestas en http://jsonapi.org/format/
		// Devolvemos el código HTTP 201 Created – [Creada] Respuesta a un POST que resulta en una creación. Debería ser combinado con un encabezado Location, apuntando a la ubicación del nuevo recurso.
		$response = Response::make(json_encode(['Pago'=>$nuevoPago]), 201)->header('Location','http://ads.deskode.local/api/pagos/'.$nuevoPago->IdPago)->header('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($idCliente,$idPago)
	{
		//
		return "Se muestra Pago $idPago del Cliente $idCliente";
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($idCliente,$idPago)
	{
		// Comprobamos si el Cliente que nos están pasando existe o no.
		$Cliente=Cliente::find($idCliente);

		// Si no existe ese Cliente devolvemos un error.
		if (!$Cliente)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Cliente con ese código.'])],404);
		}

		// El Cliente existe entonces buscamos el Pago que queremos editar asociado a ese Cliente.
		$Pago = $Cliente->pagos()->find($idPago);

		// Si no existe ese Pago devolvemos un error.
		if (!$Pago)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Pago con ese código asociado al Cliente.'])],404);
		}


		// Listado de campos recibidos teóricamente.
		$Pago1=$request->input('Pago');
		$FechaPago=$request->input('FechaPago');
		$ProxPago=$request->input('ProxPago');
		$Estatus=$request->input('Estatus');

		// Necesitamos detectar si estamos recibiendo una petición PUT o PATCH.
		// El método de la petición se sabe a través de $request->method();
		if ($request->method() === 'PATCH')
		{
			// Creamos una bandera para controlar si se ha modificado algún dato en el método PATCH.
			$bandera = false;

			// Actualización parcial de campos.
			if ($Pago1!=null&&$Pago1!='')
			{
				$Pago->Pago = $Pago1;
				$bandera=true;
			}

			if ($FechaPago!=null&&$FechaPago!='')
			{
				$Pago->FechaPago = $FechaPago;
				$bandera=true;
			}

			if ($ProxPago!=null&&$ProxPago!='')
			{
				$Pago->ProxPago = $ProxPago;
				$bandera=true;
			}

			if ($Estatus!=null&&$Estatus!='')
			{
				$Pago->Estatus = $Estatus;
				$bandera=true;
			}

			if ($bandera)
			{
				// Almacenamos en la base de datos el registro.
				$Pago->save();
				return response()->json(['Pago'=>$Pago], 200);
			}
			else
			{
				// Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
				// Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
				return response()->json(['errors'=>array(['code'=>304,'message'=>'No se ha modificado ningún dato del Pago.'])],304);
			}

		}

		// Si el método no es PATCH entonces es PUT y tendremos que actualizar todos los datos.
		if (!$Pago1 || !$FechaPago || !$ProxPago || !$Estatus)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
			return response()->json(['errors'=>array(['code'=>422,'message'=>'Faltan valores para completar el procesamiento.'])],422);
		}

		$Pago->Pago = $Pago1;
		$Pago->FechaPago = $FechaPago;
		$Pago->ProxPago = $ProxPago;
		$Pago->Estatus = $Estatus;

		// Almacenamos en la base de datos el registro.
		$Pago->save();

		return response()->json(['Pago'=>$Pago], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($idCliente,$idPago)
	{
		// Comprobamos si el Cliente que nos están pasando existe o no.
		$Cliente=Cliente::find($idCliente);

		// Si no existe ese Cliente devolvemos un error.
		if (!$Cliente)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Cliente con ese código.'])],404);
		}

		// El Cliente existe entonces buscamos el Pago que queremos borrar asociado a ese Cliente.
		$Pago = $Cliente->pagos()->find($idPago);

		// Si no existe ese Pago devolvemos un error.
		if (!$Pago)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un Pago con ese código asociado a ese Cliente.'])],404);
		}

		// Procedemos por lo tanto a eliminar el Pago.
		$Pago->delete();

		// Se usa el código 204 No Content – [Sin Contenido] Respuesta a una petición exitosa que no devuelve un body (como una petición DELETE)
		// Este código 204 no devuelve body así que si queremos que se vea el mensaje tendríamos que usar un código de respuesta HTTP 200.
		return response()->json(['code'=>204,'message'=>'Se ha eliminado el Pago correctamente.'],204);
	}
}
