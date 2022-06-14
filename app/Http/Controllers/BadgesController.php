<?php

namespace App\Http\Controllers;

use App\BadgePhoto;
use App\BadgeView;
use Illuminate\Http\Request;
use App\Repositories\BadgeRepository;
use App\Repositories\CustomersRepository;
use App\Repositories\SubscriptionRepository;
use App\User;
use App\BadgeWorkTime;
use App\Setting;
use Illuminate\Support\Facades\Validator;

class BadgesController extends Controller
{
    private $badge;
    private $subscription;
    public function __construct(BadgeRepository $badge, SubscriptionRepository $subscription){
        $this->badge = $badge;       
        $this->subscription = $subscription;
    }
    /*
    * A função abaixo traz os detalhes do crachá do id passado como parâmetro
    *
    *
    *
    *
    */
    public function find($id){

        $customers = $this->badge->findClear($id);
        $urlSite = url()->to('/');
        //Solução adaptada pra remover retorno indesejado

        foreach ($customers as $customer) {
            $photoReturn = [];

            if (isset($customer->subscription)){
                unset($customer->subscription);
            }

            if (isset($customer->photo)) {
                $customer->photo_profile_only_view = $urlSite.$customer->photo;
                unset($customer->photo);
            }
            
            if(isset($customer->photos) && sizeof($customer->photos)){
                foreach ($customer->photos as $key => $photo) {
                    $photo->uri = $urlSite.$photo->filename;
                    unset($photo->created_at);
                    unset($photo->updated_at);
                    unset($photo->badge_id);
                    unset($photo->filename);

                    $photoReturn[] = $photo;
                }

                $customer->photos_service_only_view = $photoReturn;
                unset($customer->photos);
            }
        }

        return response()->json($customers, 200);
    }



    /*
    * A função abaixo traz todos os crachás ativos do sistema
    *
    *
    *
    *
    */
    public function search(){
        $return = $this->badge->search();
        
        $urlSite = url()->to('/');
        
        foreach($return as $row){
            if(isset($row) && isset($row->photo)){
                $row->photo_profile_only_view = $urlSite.$row->photo;
                unset($row->photo);
            }
        }
        
        return response()->json($return, 200);
    }




    /*
    * A função abaixo traz crachás do usuário que solicitou a requisição
    *
    *
    *
    *
    */
    public function myBadges(Request $request){
        $return = $this->badge->myBadges($request);

        $urlSite = url()->to('/');
        
        foreach($return as $row){
            if (isset($row) && isset($row->photo)) {
                $row->photo_profile_only_view = $urlSite.$row->photo;
                unset($row->photo);
            }
        }
        
        return response()->json($return, 200);
    }


    /*
    *
    * A função abaixo cria o crachá com os dados passador na resquest
    *
    */
    public function create(Request $request){
        if (isset($request->first_name)) {
            
            if(User::where('email', $request->email)->count() > 0){
                return response()->json([
                    'msg' => 'Usuário já existe!'
                ], 400);
            }

            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'genre' => 'required|numeric',
                'document' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed',
            ]);
        }

        $badge = $this->badge->create($request);

        return response()->json([
            'badge_id' => (isset($badge) ? $badge->id : null)
        ], 201);
    }


    /*
    *
    * A função abaixo efetua o pagamento do crachá.
    *
    */
    public function subscribe(Request $request){
        
        $request->validate([
            // 'payment_method'             => 'required|string',
            'badge_id'             => 'required|numeric',
        ]);

        return $this->subscription->subscribe($request);
    }


    /*
    *
    * Função que grava os clicks do WhatsApp
    *
    */
    public function badgeWhats($id){
        
        $this->badge->badgeWhats($id);

        $return = [
            'msg' => 'Click Registrado com sucesso!'
        ];

        response()->json($return, 201);
    }


    /*
    * 
    * Função que grava os clicks do Telefone.
    *
    */
    public function badgePhone($id){

        $this->badge->badgePhone($id);

        $return = [
            'msg' => 'Click Registrado com sucesso!'
        ];
 
        return response()->json($return, 201);
    }


    /**
     * 
     * A função atualiza o crachá com os dados passados como parâmetro
     * 
     */
    public function updateBadge(Request $request, $id){

        $request->validate([
            'city_id'             => 'required|numeric',
            'category_id'         => 'required|numeric',
            'plan_id'             => 'required|numeric',
            'nickname'            => 'required|string',
            'cellphone'           => 'required|string',
            'latitude'            => 'required|string',
            'longitude'           => 'required|string',
            'zipcode'             => 'required|string',
            'uf'                  => 'required|string',
            'city'                => 'required|string',
            'street'              => 'required|string',
            'number'              => 'required|numeric',
            'district'            => 'required|string',
            'range'               => 'required|numeric',
            'description'         => 'required|string',
            'workTime'            => 'required|array'
        ]);


        $badge = $this->badge->updateBadge($request, $id);

        return response()->json([
            'badge_id' => (isset($badge) ? $badge->id : null)
        ], 201);
        
    }

    /**
     * 
     * A função cria um review para o crachá com os dados passados como parâmetro
     * 
     */
    public function createReview(Request $request, $id){

        $request->validate([
            'quality'             => 'required|integer|between:1,5',
            'punctuality'         => 'required|integer|between:1,5',
            'attendance'          => 'required|integer|between:1,5',
            'ip'                  => 'required|string'
        ]);

        return $this->badge->createReview($request, $id);        
    }

    /**
     * 
     * 
     */
    public function getReviews($id){
        
        return $this->badge->getReviews($id);

    }

    /**
     * Função para registrar a alteração de plano
     */
    public function changePlanProcess(Request $request){

        $request->validate([
            'badge_id' => 'required|numeric',
            'plan_id'  => 'required|numeric'
        ]);


        $badge = $this->badge->get($request->badge_id);

        if($badge->customer_id != $request->user()->id){
            return response()->json([
                'msg' => 'Problemas na requisição!'
            ], 400);
        }
        
        if ($badge->subscription->gateway_status != 'canceled_by_user' && $badge->subscription->gateway_status != 'canceled') {
            $this->subscription->cancel($badge);
        }
            
            $badge->plan_id = $request->plan_id;
            $badge->save();

            return response()->json([
                'data' => $badge
            ], 200);
    }

    /**
     * Função para cancelar o crachá
     */
    public function cancelBadge(Request $request){

        $request->validate([
            'badge_id' => 'required|numeric'
        ]);

        $badge = $this->badge->get($request->badge_id);

        if($badge->customer_id != $request->user()->id){
            return response()->json([
                'msg' => 'Problemas na requisição!'
            ], 404);
        }

        if ($badge->subscription->gateway_status != 'canceled_by_user' && $badge->subscription->gateway_status != 'canceled') {
            $this->subscription->cancel($badge);
        } 

        return response()->json([
            'msg' => 'Crachá cancelado com sucesso!'
        ], 200);

    }

    /**
     * Função para deletar o crachá no banco
     */
    public function deleteBadge(Request $request){
        $request->validate([
            'badge_id' => 'required|numeric'
        ]);

        $badge = $this->badge->get($request->badge_id);

        if(!$badge || $badge->customer_id != $request->user()->id){
            return response()->json([
                'msg' => 'Problemas na requisição!'
            ], 404);
        }

        if ($badge->subscription->gateway_status != 'canceled_by_user' && $badge->subscription->gateway_status != 'canceled') {
            return response()->json([
                'msg' => 'Você não pode excluir um crachá que não esteja cancelado!'
            ], 404);
        }

        $badge->delete();

        return response()->json([
            'msg' => 'Crachá deletado com sucesso!'
        ], 204);
    }

    /**
     * 
     */
    public function uploadPhotosByFile(Request $request){
        $rules = [
            'badge_id' => 'required|numeric',
            'count_photo_profile' => 'required|numeric',
            'count_photos_service' => 'required|numeric',
            'count_photo_document' => 'required|numeric'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(400);
        }
        
        $upPhoto = $this->badge->uploadPhotosByFile($request);
        
        if ((isset($upPhoto) && is_object($upPhoto)) && $upPhoto->error === false) {
            return response()->json(200);
        } else {
            $badge = $this->badge->getTemp($request->badge_id);
            if(isset($badge)){
                $badgePhotos = BadgePhoto::where('badge_id', $request->badge_id)->get();
                
                foreach($badgePhotos as $badgePhoto){
                    $badgePhoto->forceDelete();
                }
                
                $badgeWorkTimes = BadgeWorkTime::where('badge_id', $request->badge_id)->get();
                foreach($badgeWorkTimes as $badgeWorkTime){
                    $badgeWorkTime->forceDelete();
                }

                $badgeViews = BadgeView::where('badge_id', $request->badge_id)->get();
                foreach($badgeViews as $badgeView){
                    $badgeView->forceDelete();
                }

                $badge->forceDelete();
                User::where(['id' => $badge->customer_id, 'temp' => 1])->first()->forceDelete();
            }
            return response()->json($upPhoto, 400);
        }
        
    }

    public function uploadEditPhotoSite(Request $request){
        $rules = [
            'badge_id' => 'required|numeric',
            'count_photos_service' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(400);
        }

        $upPhoto = $this->badge->uploadPhotosByFile($request);
        if (isset($upPhoto) && $upPhoto->error === false) {
            return response()->json(200);
        } else {
            return response()->json(400);
        }
    }

    public function deleteEditPhotoSite(Request $request){
        $rules = [
            'id' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(400);
        }

        $badgePhoto = BadgePhoto::find($request->id);
        if ($badgePhoto && is_object($badgePhoto)) {
            if(isset($badgePhoto) && $badgePhoto->filename){
                unlink(public_path().$badgePhoto->filename);
            }

            BadgePhoto::destroy($request->id);
        }
    }

    public function getMaxPhotosService(){
        $settings = Setting::where('tag', 'max_photos_service')->first();

        return response()->json($settings->value, 200);
    }
}
