<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Coupon;
use App\CouponsHistory;

class CouponRepository
{
    /**
    * @var Coupon
    * @var History
    */
    private $coupon;
    private $history;
    
    public function __construct(Coupon $coupon, CouponsHistory $history){
        $this->coupon = $coupon;
        $this->history = $history;        
    }

    public function getAll(){

        $coupons = $this->coupon->select('name', 'description', 'plan_id', 'initial_date', 'end_date')
            ->with('plan')
            ->active()
            ->paginate(10);

        if ($coupons->isEmpty()){
            $return = [
                'msg' => 'Não foi possivel carregar os cupons!'
            ];
        }else {
            $return = $coupons;
        }
 
        return response()->json($return, 200);
        
    }

    public function find($code){
        $coupon = $this->coupon->select('id', 'quantity', 'name', 'description', 'initial_date', 'end_date')
            ->with('plans')
            ->active()
            ->where('code', $code)
            ->first();

        $now = date("Y-m-d H:i:s");
        
        if (!$coupon || $coupon->plans->isEmpty()){
            $return = [
                'msg' => 'Desculpe, esse cupom não existe ou não está mais disponível!'
            ]; 
        }else {
            $leftQuantity = $coupon->quantity - $this->history->where('coupon_id', $coupon->id)->count();
            if($coupon->initial_date <= $now && $coupon->end_date >= $now && $leftQuantity > 0){
                foreach ($coupon->plans as $plan) {
                    $plan->amount = number_format($plan->amount,2,",",".");
                }
                
                $return['data'] = $coupon;
            }else{
                $return = [
                    'msg' => 'Cupom sem quantidade, expirado ou fora da data de validade!'
                ];
            }
            
        }
 
        return $return;                
    }

    public function couponApply(){
                
    }
}