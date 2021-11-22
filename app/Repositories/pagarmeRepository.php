<?php

namespace App\Repositories;

use PagarMe\Client;
use Config;

class PagarmeRepository
{
    private $pagarme;
    private $post_back_url;

    public function __construct(){
        $this->pagarme = new Client(Config::get('pagarme.api_key'));
        $this->post_back_url = Config::get('url.web').'/postback/payment-gateway';
    }

    public function subscribe($data, $badge){

        $phone = str_replace(['(', ')', ' ', '-'], "", $badge->phone);
        $phone_ddd = mb_substr($phone, 0, 2);
        $phone_number  = mb_substr($phone, 2);
        $card_expiration_date = str_replace('/', '', $data->card_expiration_date);

        $subscription = $this->pagarme->subscriptions()->create([
            'plan_id' => $badge->plan->gateway_id,
            'payment_method' => 'credit_card',
            'card_number' => $data->card_number,
            'card_holder_name' => $data->card_holder_name,
            'card_expiration_date' => $card_expiration_date,
            'card_cvv' => $data->card_cvv,
            'postback_url' => $this->post_back_url,
            'customer' => [
                'email' => $badge->customer->email,
                'name' => $data->card_holder_name,
                'document_number' => $data->card_holder_document,
                'address' => [
                    'street' => $badge->street,
                    'street_number' => $badge->number,
                    'complementary' => '',
                    'neighborhood' => $badge->district,
                    'zipcode' => $badge->zipcode,
                ],
                'phone' => [
                    'ddd' => $phone_ddd,
                    'number' => $phone_number
                  ],
            ],
        ]);

        return $subscription;
        
    }

    public function subscribeBillet($badge){

        $subscription = $this->pagarme->subscriptions()->create([
            'plan_id' => $badge->plan->gateway_id,
            'payment_method' => 'boleto',
            'postback_url' => $this->post_back_url,
            'customer' => [
                'email' => $badge->customer->email,
                'name' => $badge->customer->first_name . ' ' . $badge->customer->last_name,
                'document_number' => $badge->customer->document,
            ],
        ]);

        return $subscription;
        
    }

    public function createCreditCard($data){
        $card = $this->pagarme->cards()->create([
            'holder_name' => 'Yoda',
            'number' => '4242424242424242',
            'expiration_date' => '1225',
            'cvv' => '123'
        ]);

        return $card;
    }

    public function modifySubsription($data){
        $updatedSubscription = $this->pagarme->subscriptions()->update([
            'id' => 1234,
            'plan_id' => 4321,
            'payment_method' => 'boleto'
        ]);
        
        return $updatedSubscription;
    }

    public function cancelSubscription($badge){
        $canceledSubscription = $this->pagarme->subscriptions()->cancel([
            'id' => $badge->subscription->gateway_id
        ]);        

        return $canceledSubscription;
    }

    public function createPlan($data){
        $plan = $this->pagarme->plans()->create([
            'amount' => '15000',
            'days' => '30',
            'name' => 'The Pro Plan - Platinum - Best ever'
        ]);  
        
        return $plan;
    }
    
}
