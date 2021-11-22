<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Plan;

class PlansController extends Controller
{
    /**
     * Função que retorna todos os planos cadastrados
     */
    
    public function getall(){
        $data = Plan::where('coupon', 0)->active()->get();

        if ($data->isEmpty()) {
            $return = [
                'msg' => 'Não existe nenhum plano ativo no momento!'
            ];
        }else {
            foreach ($data as $plan) {
                $plan->amount = number_format($plan->amount,2,",",".");
            }
            $return = [
                'data' => $data
            ];
        }
        
        return response()->json($return, 200);
    }
}
