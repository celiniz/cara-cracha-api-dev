<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CouponRepository;

class CouponsController extends Controller
{
    private $coupon;

    public function __construct(CouponRepository $coupon){
        $this->coupon = $coupon;        
    }

    /**
     * Retorna o cupom com o ID passado como parâmetro
     */

    public function find($id){
        
        $return = $this->coupon->find($id);   
        
        return response()->json($return, 200);
    }
}
