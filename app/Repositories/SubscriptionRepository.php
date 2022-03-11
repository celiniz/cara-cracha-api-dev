<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Subscription;
use App\Badge;
use App\User;
use App\BadgeTransaction;
use App\CustomerCreditCard;
use App\Plan;
use App\ChangePlanLog;
use App\Coupon;
use App\Transaction;
use App\CouponsHistory;
use App\PaymentMethod;
use App\Repositories\PagarmeRepository;
use PagarMe\Endpoints\Subscriptions;

class SubscriptionRepository
{
    private $pagarmeRepository;
    private $badge;
    private $subscription;
    private $badgeTransaction;
    private $customerCreditCard;
    private $transaction;

    public function __construct(PagarmeRepository $pagarmeRepository, Badge $badge, Subscription $subscription, BadgeTransaction $badgeTransaction, CustomerCreditCard $customerCreditCard, Transaction $transaction)
    {
        $this->pagarmeRepository = $pagarmeRepository;
        $this->badge = $badge;
        $this->subscription = $subscription;
        $this->badgeTransaction = $badgeTransaction;
        $this->customerCreditCard = $customerCreditCard;
        $this->transaction = $transaction;
    }

    public function subscribe(Request $request)
    {
        
        $badge = $this->badge->with(['customer', 'plan'])->find($request->badge_id);

        if ($badge == null) {
            return response()->json([
                'msg' => 'Plano incorreto ou sem código de intgração do PagarMe'
            ], 400);
        }

        if (!isset($request->payment_method)) {
            $request->payment_method = "credit_card";
        }

        if(isset($request->dev)){
            $subscription = new Subscription;

            $subscription->plan_id = 10;
            $subscription->gateway_id = 1111111111;
            $subscription->gateway_status = 'trialing';
            $subscription->start_at = date("Y-m-d H:i:s");
            $subscription->end_at = date("Y-m-d H:i:s", strtotime('2022-10-02'));
            $subscription->save();
    
            $badge->subscription_id = $subscription->id;
    
            $badge->save();

            $arrRetorno = (object) [
                'billet_barcode' => 111111111111111111111111111111111111,
                'amount' => '178,32',
                'billet_url' => 'https://www.boletobancario.com/boletofacil/img/boleto-facil-exemplo.pdf',
                'expiration_date' => '10/05/2022'
            ];

            return response()->json($arrRetorno, 201);
        }

        switch ($request->payment_method) {
            case 'credit_card':

                $request->validate([
                    'card_number'          => 'required|string',
                    'card_holder_name'     => 'required|string',
                    'card_expiration_date' => 'required|string',
                    'card_cvv'             => 'required|string',
                    'card_holder_document' => 'required|string',
                ]);

                try {

                    $subscription = $this->pagarmeRepository->subscribe($request, $badge);
                } catch (\Exception $e) {

                    return response()->json([
                        'msg' => 'Houve um problema ao realizar a transação.'
                    ], 400);
                }

                //return response()->json($subscription, 200);

                if (isset($subscription->object) && $subscription->object == 'subscription' && ($subscription->status == 'paid' || $subscription->status == 'trialing')) {

                    $this->subscription = new Subscription;

                    $this->subscription->plan_id = $badge->plan->id;
                    $this->subscription->gateway_id = $subscription->id;
                    $this->subscription->gateway_status = $subscription->status;
                    $this->subscription->start_at = date("Y-m-d H:i:s", strtotime($subscription->current_period_start));
                    $this->subscription->end_at = date("Y-m-d H:i:s", strtotime($subscription->current_period_end));
                    $this->subscription->save();

                    $badge->subscription_id = $this->subscription->id;
                    $badge->save();

                    if ($subscription->status == 'paid') {
                        $this->transaction = new Transaction;

                        $this->transaction->badge_id = $badge->id;
                        $this->transaction->payment_method_id = 2;
                        $this->transaction->subscription_id = $this->subscription->id;
                        $this->transaction->plan_id = $badge->plan->id;
                        $this->transaction->status_id = 5;
                        $this->transaction->amount = $subscription->plan->amount;
                        $this->transaction->gateway_id = $subscription->current_transaction->tid;
                        $this->transaction->tid = $subscription->current_transaction->tid;

                        $this->transaction->save();
                    }

                    $this->customerCreditCard->create([
                        'customer_id' => $badge->customer_id,
                        'card_id'     => $subscription->card->id,
                        'brand'       => $subscription->card->brand,
                        'last_digits' => $subscription->card->last_digits
                    ]);
                }

                break;

            case 'billet':
                
                try {

                    $subscription = $this->pagarmeRepository->subscribeBillet($badge);
                } catch (\Exception $e) {

                    return response()->json([
                        'msg' => 'Houve um problema ao realizar a transação.'
                    ], 400);
                }

                //return response()->json($subscription, 200);

                if (isset($subscription->object) && $subscription->object == 'subscription' && ($subscription->status == 'trialing' || $subscription->status == 'unpaid')) {

                    $this->subscription = new Subscription;

                    $this->subscription->plan_id = $badge->plan->id;
                    $this->subscription->gateway_id = $subscription->id;
                    $this->subscription->gateway_status = $subscription->status;
                    $this->subscription->start_at = date("Y-m-d H:i:s", strtotime($subscription->current_period_start));
                    $this->subscription->end_at = date("Y-m-d H:i:s", strtotime($subscription->current_period_end));
                    $this->subscription->save();

                    $badge->subscription_id = $this->subscription->id;
                    $badge->save();

                    $this->transaction = new Transaction;

                    $this->transaction->badge_id                = $badge->id;
                    $this->transaction->payment_method_id       = 5;
                    $this->transaction->subscription_id         = $this->subscription->id;
                    $this->transaction->plan_id                 = $badge->plan->id;
                    $this->transaction->status_id               = 1;
                    $this->transaction->amount                  = $badge->plan->amount;
                    $this->transaction->gateway_id              = $subscription->current_transaction->tid;
                    $this->transaction->tid                     = $subscription->current_transaction->tid;
                    $this->transaction->billet_url              = $subscription->current_transaction->boleto_url;
                    $this->transaction->billet_barcode          = $subscription->current_transaction->boleto_barcode; 
                    $this->transaction->billet_expiration_date  = date("Y-m-d H:i:s", strtotime($subscription->current_transaction->boleto_expiration_date));

                    $this->transaction->save();

                    $this->transaction->expiration_date  = $this->transaction->billet_expiration_date->format('d/m/Y');
                    
                }

                break;
        }

        if (isset($request->coupon_code) && $request->coupon_code != null && $request->coupon_code != '') {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            if ($coupon) {
                $history = new CouponsHistory;
                $history->badge_id = $badge->id;
                $history->coupon_id = $coupon->id;
                $history->save();
            }
        }
        
        if (isset($request->indication) && $request->indication != null && isset($request->newUser) && $request->newUser == true) {
            $this->getIndication($request->indication, $badge);
        }

        $changePlanLog = new ChangePlanLog;
        $changePlanLog->badge_id = $badge->id;
        $changePlanLog->plan_id = $badge->plan_id;
        $changePlanLog->payment_method = $request->payment_method;
        $changePlanLog->save();

        $this->transaction->amount = number_format($this->transaction->amount,2,",",".");

        return response()->json($this->transaction, 201);
    }

    /**
     * 
     */
    public function getIndication($indication, $badge){

        $indicator = User::where('code', $indication)->first();
        
        $customer = User::find($badge->customer_id);
        
        if($indicator != null){            

            $customer->parent_id = $indicator->id;

            $customer->save();

        }
    }

    public function cancel($badge){

        try 
		{
            $template = $this->pagarmeRepository->cancelSubscription($badge);

            if ($template->status == 'canceled') 
			{
				// Make badge unavailable.
				$badge->active = 0;
				$badge->save();

				// Facade status: "canceled_by_user":"canceled".
				$subscription = $badge->subscription;
				$subscription->gateway_status = 'canceled_by_user';
				$subscription->save();

				return true;
			}

			return response()->json([
                'msg' => 'Problemas na requisição!'
            ], 400);
		} 
		catch (\Exception $e)
		{
			return response()->json([
                'msg' => 'Problemas na requisição!'
            ], 400);
		}
                
    }
    
}
