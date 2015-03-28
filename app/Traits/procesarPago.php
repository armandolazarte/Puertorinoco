<?php
/**
 * Created by jorgelsaud.
 * User: jorgelsaud
 * Date: 23/3/15
 * Time: 17:42
 */

namespace App\Traits;


trait procesarPago {
    protected static function boot()
    {
        parent::boot();
        static::created(function($pago){
            $pago->procesar();
        });
        static::deleting(function($pago){
            $pago->procesarBorrado();
        });
    }
    private function procesar(){
        $reserva=$this->reserva;
        $pagos=$reserva->pagos->sum('monto');
        $totalDeuda=$reserva->montoTotal-$pagos;
        $reserva->deudaRestante=$totalDeuda;
        if($totalDeuda<=0){
            $reserva->estado_del_pago_id=4;
            if($totalDeuda<0){
                $reserva->deudaRestante=0;
                $reserva->cliente->credito=$reserva->cliente->credito-$totalDeuda;
                $reserva->cliente->save();
            }
        }
        if($totalDeuda>0&&$totalDeuda<$reserva->montoTotal&&$reserva->estado_del_pago_id!=2){
            $reserva->estado_del_pago_id=4;
        }
        $reserva->save();
    }
    private function procesarBorrado(){
        $reserva=$this->reserva;
        $pagos=$reserva->pagos->sum('monto') - $this->monto;
        $totalDeuda=$reserva->montoTotal;
        $reserva->deudaRestante=$totalDeuda-$pagos;
        if($totalDeuda<=0)
        {
            $reserva->estado_del_pago_id=4;
        }
        if($totalDeuda>0)
        {
            if($reserva->estado_del_pago_id!=2&&$pagos<=0){
                $reserva->estado_del_pago_id=1;
            }
            if($pagos>0){
                $reserva->estado_del_pago_id=3;
            }
        }
        $reserva->save();
    }

}