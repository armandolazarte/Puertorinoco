<?php namespace App\Http\Controllers;

use App\Cliente;
use App\Embarcacion;
use App\Http\Requests;
use App\Http\Requests\ReservacionesRequest;
use App\Paseo;
use App\Reservacion;
use Illuminate\Auth\Guard;
use Illuminate\Support\Facades\Lang;

class ReservacionController extends Controller {

    /**
     * @var Guard
     */
    private $auth;

    function __construct(Guard $auth)
    {

        $this->auth = $auth;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $embarcaciones = Embarcacion::all();
        $paseos = Paseo::all();

        return view('reservacion.create', compact('embarcaciones', 'paseos'));
    }

    /**
     * @param ReservacionesRequest $request
     * @return $reserva
     */
    private function RealizarReserva($datos)
    {
        $reserva = Reservacion::create($datos);
        $reserva->actualizaMontoTotal();

        //dd($reserva);
        return $reserva;

    }

    /**
     * @param $fecha
     * @param $clienteId
     * @param $embarcacionId
     * @param $paseoId
     * @return mixed
     */
    private function vecesReservaRepetida($fecha, $clienteId, $embarcacionId, $paseoId)
    {
        return Reservacion::whereFecha($fecha)->whereClienteId($clienteId)->whereEmbarcacionId
        ($embarcacionId)->wherePaseoId($paseoId)->count();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ReservacionesRequest $request)
    {
        $cliente = $this->ActualizarOCrearCliente($request);
        $vecesRepetida = Reservacion::ObtenerVecesQueSeRepite($request->input('fecha'), $cliente->id,
            $request->input('embarcacion_id'), $request->input('paseo_id'))->count();

        //dd($vecesRepetida);
        if (($this->auth->guest() || !$this->auth->user()->nivelDeAcceso->permiso->esAgencia) && $vecesRepetida > 0)
        {
            flash()->error(Lang::get('formulario.reservaDuplicada'));

            return redirect()->back()->withInput();
        }
        //verificar que Hay Cupos
        $respuesta = $request->all() + ['cliente_id' => $cliente->id];

        $reservacion = $this->RealizarReserva($respuesta);

        return view('reservacion.mostrar',compact('reservacion','cliente'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $reservacion=Reservacion::findOrFail($id);
        $totalCuposEnPaseo=Reservacion::PasajerosReservadosDeLaFechaEmbarcacionyPaseo($reservacion->fecha,
            $reservacion->embarcacion_id,$reservacion->paseo_id);
        //dd($totalCuposEnPaseo);
        return view('reservacion.mostrar',compact('reservacion','totalCuposEnPaseo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param ReservacionesRequest $request
     * @return static
     */
    public function ActualizarOCrearCliente(ReservacionesRequest $request)
    {
        $cliente = Cliente::whereIdentificacion($request->input('identificacion'))
            ->orWhere('email', '=', $request->input('email'))
            ->orWhere('telefono', '=', $request->input('telefono'))
            ->get();
        if ($cliente->count() == 0)
        {

            return $cliente = Cliente::create($request->all());
        }
        $cliente = $cliente->first();
        $cliente->fill($request->input())->save();

        return $cliente;
    }

}