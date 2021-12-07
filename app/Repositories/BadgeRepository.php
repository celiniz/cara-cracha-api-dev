<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Badge;
use App\User;
use App\BadgePhoto;
use App\BadgeReview;
use App\BadgeWorkTime;
use App\Category;
use GuzzleHttp\Client;
use App\CategoryNotFound;
use App\Transaction;
use App\Subscription;
use App\Api_setting;
use DB, Str, Config, Image;
use Symfony\Component\Console\Input\Input as SymfonyInput;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class BadgeRepository
{
    /**
	 * @var Badge
	 */
    private $badge;
    
	public function __construct(Badge $badge)
	{
        $this->badge = $badge;       
    }

    /**
     * Função para a criação do crachá caso o usuario tenha conta ou não
     * 
     */
    public function create(Request $request){

        $user = $this->userBadge($request);

        $request->validate([
            'city_id'             => 'required|numeric',
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

        $badge = new Badge;

        if ($request->category_id == null) {
            $category = new Category;
            $category->active = 0;
            $category->featured = 0;
            $category->name = $request->category;
            $category->save();

            $notFound = new CategoryNotFound;
            $notFound->name = $category->name;
            $notFound->email = $user->email;
            $notFound->category_id = $category->id;
			$notFound->origin = 2;
            $notFound->status = 1;
            $notFound->save();

            $badge->category_id = $category->id;
        }else {
            $badge->category_id = $request->category_id;
        }

        if (isset($request->photo) && $request->photo != null) {
            // Pega foto do crachá
            $data = $request->photo;
            $name = uniqid();
            $extension = explode('/', mime_content_type($data))[1];
            $filepath = '\images\badges\\'.$name.'.'.$extension;

            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace('data:image/jpeg;base64,', '', $data);
            $data = str_replace('data:image/jpg;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            file_put_contents(public_path($filepath), $data);

            if (file_exists(public_path($filepath))) {
                $badge->photo = $filepath;
            }
        }

        $badge->customer_id = $user->id;
        $badge->city_id = $request->city_id;
        $badge->plan_id = $request->plan_id;
        $badge->active = 0;
        $badge->code = mt_rand(1000000, 9999999);
        $badge->nickname = $request->nickname;
        $badge->phone = $request->cellphone;
        $badge->cellphone = $request->cellphone;
        $badge->latitude = $request->latitude;
        $badge->longitude = $request->longitude; 
        $badge->zipcode = str_replace('-', '', $request->zipcode);
        $badge->uf = $request->uf;
        $badge->city = $request->city;
        $badge->street = $request->street ;
        $badge->number = $request->number ;
        $badge->district = $request->district ;
        if (isset($request->complement)) $badge->complement = $request->complement ;
        $badge->range = ceil($request->range) ;
        $badge->description = $request->description ;

        $badge->save();

        if (isset($request->photos) && $request->photos != null) {
            $this->uploadPhotos($badge->id, $request->photos);
        }


        if (isset($request->workTime) && $request->workTime != null) {
            $this->storeWorkTimes($badge->id, $request->workTime);
        }

		return $badge;
    }

    /**
     * Função que cria um usuário caso esteja criando um profissional
     * 
     */
    public function userBadge(Request $request){
        $user = '';

        if (isset($request->first_name)) {         
    
            $newUser = new User([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'genre' => $request->genre,
                'document' => str_replace(['.', '-'], '', $request->document),
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'code' => mt_rand(1000000, 9999999),
            ]);

            if ($newUser->genre == 3) {
                $newUser->genre = 0;
            }

            /**
             * Pega foto do documento
             */
            $data = $request->document_photo;
            $extension = explode('/', mime_content_type($data))[1];

            $name = uniqid();
            $filepath = '\images\document_photo\\'.$name.'.'.$extension;

            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace('data:image/jpeg;base64,', '', $data);
            $data = str_replace('data:image/jpg;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            file_put_contents(public_path($filepath), $data);
        
            if (file_exists(public_path($filepath))) {
                $newUser->document_photo = $filepath;
                $newUser->save();
            }

            $user = $newUser;

        }else {
            $currentlyUser = User::find($request->user()->id);

            if (empty($currentlyUser->document)) {
                $request->validate([
                    'document' => 'required|string',
                    'document_photo_name' => 'required|string',
                ]);

                $currentlyUser->document = $request->document;

                /**
                 * Pega foto do documento
                 */

                $currentlyUser->document_photo  = $request->document_photo;

                $currentlyUser->save();

            }

            $user = $currentlyUser;
        }

        return $user;
    }
    

    /*
    * A função abaixo traz os detalhes do crachá do id passado como parâmetro
    *
    *
    *
    *
    */
    public function findClear($id){

        $badge = $this->badge->reviewsPercentage()->with([
            'photos', 
            'category' => function($query) {
                $query->selectRaw('parent_id, id, name, slug');
            }, 
            'reviews'=> function($query) {
                $query->select('id', 'customer_id', 'badge_id', 'reviewer_customer_id', 'average', 'comment', 'updated_at');
                $query->where([
                    ['approved', '=', 1],
                    ['comment', '!=', ''],
                ]);
                $query->take(3);
            },  
            'workTime' => function($query) {
                $query->orderByRaw("FIELD(day, 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo')");
            }])
            ->find($id);

        if (!isset($badge)){
            $return = [
                'msg' => 'Desculpe, esse crachá não existe ou não está mais disponível!'
            ]; 
        } else {
            $badge->reviews = $this->reviewsMessage($badge->reviews);
            $badge->reviewsCount = BadgeReview::where('badge_id', $badge->id)->approved()->count();
            $badge->canceled = ($badge->subscription->gateway_status == 'canceled_by_user' || $badge->subscription->gateway_status == 'canceled')? true : false;
            $return['data'] = $badge;
            $badge->views()->create([
                'customer_id' => $badge->customer_id,
                'badge_id' => $badge->id
            ]);
        }
 
        return $return;
    }

    public function find($id){

        $badge = $this->badge->reviewsPercentage()->with([
            'photos', 
            'category', 
            'reviews'=> function($query) {
                $query->where([
                    ['approved', '=', 1],
                    ['comment', '!=', ''],
                ]);
                $query->take(3);
                },  
            'workTime' => function($query) {
                $query->orderByRaw("FIELD(day, 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo')");
                }, 
            'customer'])
            ->subscribed()
            ->find($id);

        
        if (!isset($badge)){
            $return = [
                'msg' => 'Desculpe, esse crachá não existe ou não está mais disponível!'
            ]; 
        }else {
            $badge->reviews = $this->reviewsMessage($badge->reviews);
            $badge->reviewsCount = BadgeReview::where('badge_id', $badge->id)->approved()->count();
            $badge->canceled = ($badge->subscription->gateway_status == 'canceled_by_user' || $badge->subscription->gateway_status == 'canceled')? true : false;
            $return['data'] = $badge;
            $badge->views()->create([
                'customer_id' => $badge->customer_id,
                'badge_id' => $badge->id
            ]);
        }
 
        return $return;
    }

    /**
     * 
     * 
     */
    public function get($id){
        return $this->badge->find($id);        
    }



    /*
    * A função abaixo traz todos os crachás ativos do sistema com os filtros solicitados
    *
    *
    *
    *
    */
    public function search(){

        $badges = $this->badge->where('category_id', Input::get('category_id'))
            ->subscribed()
            ->active()
            ->FilterForDisplay()
            ->select('id', 'nickname', 'photo', 'recommended_percentage')
            ->addSelect(DB::raw('(6371 * acos(cos(radians('.Input::get('lat').')) * cos(radians(latitude)) * cos(radians('.Input::get('lon').') - radians(longitude)) + sin(radians('.Input::get('lat').')) * sin(radians(latitude)))) AS distance'))
            ->addSelect('plus')
            ->orderBy('plus', 'desc')
            ->paginate(10);

        if ($badges->isEmpty()){
            $return = [
                'msg' => 'Não foi possivel carregar os crachás!'
            ]; 
        }else {
            foreach ($badges as $badge) {
                $badge->distance = round($badge->distance);
            }
            $return = $badges;
        }
 
        return $return;
    }




    /*
    * A função abaixo traz crachás do usuário que solicitou a requisição
    *
    *
    *
    *
    */
    public function myBadges(Request $request){
        $badges = $this->badge->join('badge_categories', 'badge_categories.id', '=', 'badges.category_id')
                              ->where('customer_id', $request->user()->id)
                              ->subscribed()
                              ->reviewsPercentage()
                              ->select('badges.id', 'badges.active', 'badges.nickname', 'badges.photo', 'badge_categories.name AS categoryName', 'badges.subscription_id AS status', 'recommended_percentage')
                              ->addSelect(DB::raw('(select count(*) from badge_clicks where badge_clicks.badge_id = badges.id and type_of_click = "whats_click") as whats_click'))
                              ->addSelect(DB::raw('(select count(*) from badge_clicks where badge_clicks.badge_id = badges.id and type_of_click = "phone_click") as phone_click'))
                              ->addSelect(DB::raw('(select count(*) from badge_views where badge_views.badge_id = badges.id) as views'))
                              ->paginate(10);
        
        if ($badges->isEmpty()){
            $return = [
                'msg' => 'Não existem crachás para esse usuário!'
            ]; 
        }else {
            foreach ($badges as $badge) {
                $badge->status = $this->getWrittenStatus($badge);
            }
            $return = $badges;
        }
 
        return $return;
    }


    /*
    * A função abaixo registra o click no telefone
    *
    *
    *
    *
    */
    public function badgePhone($id){

        $badge = $this->badge->find($id);

        $badge->clicks()->create([
            'customer_id' => $badge->customer_id,
            'badge_id' => $badge->id,
            'type_of_click' => 'phone_click'
        ]);

        return null;
    }

    /*
    * A função abaixo traz os detalhes dos crachás conforme os filtros passados como parâmetro
    *
    *
    *
    *
    */
    public function badgeWhats($id){

        $badge = $this->badge->find($id);

        $badge->clicks()->create([
            'customer_id' => $badge->customer_id,
            'badge_id' => $badge->id,
            'type_of_click' => 'whats_click'
        ]);
 
        return null;
    }

    /**
     * Faz o upload das imagens do crachá para a tabela BagdePhotos no banco de dados
     * 
     */
    public function uploadPhotos($badge, $photos){

        foreach ($photos as $photo) {
            $data = $photo['img'];

            $mime = mime_content_type($data);
            $extension = explode('/', $mime)[1];

            $name = uniqid();
            $filepath = '\images\badge_photos\\'.$name.'.'.$extension;
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace('data:image/jpeg;base64,', '', $data);
            $data = str_replace('data:image/jpg;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            file_put_contents(public_path($filepath), $data);
            
            if (file_exists(public_path($filepath))) {
                $badgephoto = new BadgePhoto();
                $badgephoto->badge_id = $badge;
                $badgephoto->filename = $filepath;
                $badgephoto->save();
            }
        }

        return null;

    }


    /**
     * Grava o horário de atendimento
     * 
     */
    public function storeWorkTimes($id, $worktime){
        foreach ($worktime as $time) {
            $badgeorktime = new BadgeWorkTime();
            $badgeorktime->day = $time['day'];
            $badgeorktime->initial_time = $time['initial_time'];
            $badgeorktime->final_time = $time['final_time'];
            $badgeorktime->badge_id = $id;
            $badgeorktime->save();
        }
                
    }

    /**
     * Apaga todos os horários de atendimento do usuário
     * 
     */
    public function removeWorkTimes($id){
        BadgeWorkTime::where('badge_id', $id)->delete();              
    }

    /**
     * Atualiza o crachá do usuário
     * 
     */
    public function updateBadge(Request $request, $id){

        $badge = Badge::find($id);        


        if (isset($request->photo) && $request->photo != null) {
            /**
             * Pega foto do crachá
             */
            $badge->photo = $request->photo;
    
        }

        $badge->category_id = $request->category_id;
        $badge->customer_id = $request->user()->id;
        $badge->city_id = $request->city_id;
        $badge->plan_id = $request->plan_id;
        $badge->active = 0;
        $badge->code = mt_rand(1000000, 9999999);
        $badge->nickname = $request->nickname;
        $badge->phone = $request->cellphone;
        $badge->cellphone = $request->cellphone;
        $badge->latitude = $request->latitude;
        $badge->longitude = $request->longitude; 
        $badge->zipcode = $request->zipcode;
        $badge->uf = $request->uf;
        $badge->city = $request->city;
        $badge->street = $request->street;
        $badge->number = $request->number ;
        $badge->district = $request->district ;
        if (isset($request->complement)) $badge->complement = $request->complement ;
        $badge->range = ceil($request->range) ;
        $badge->description = $request->description;
        
        $badge->save();

        if (isset($request->photos_delete) && $request->photos_delete != null) {
            foreach ($request->photos_delete as $delete) {
                BadgePhoto::destroy($delete);
            }
        }

        if (isset($request->photos_insert) && $request->photos_insert != null) {
            $this->uploadPhotos($badge->id, $request->photos_insert);
        }

        // BadgePhoto::where('badge_id', $badge->id)->delete();
        // BadgeWorkTime::where('badge_id', $badge->id)->delete();


        if (isset($request->workTime) && $request->workTime != null) {
            $this->removeWorkTimes($badge->id);
            $this->storeWorkTimes($badge->id, $request->workTime);
        }

		return $badge;
        
    }


    /**
	 * Retorna uma string com o status do crachá
	 *
	 */
	public function getWrittenStatus($badge)
	{
        $transaction = Transaction::where('badge_id', $badge->id)->latest('id')->first();

        $subscription = Subscription::where('id', $badge->status)->first();
		
		if ($subscription->gateway_status == 'canceled_by_user') return 'Cancelado.';

		if ($subscription->gateway_status == 'canceled') return 'Não aprovado.';

		if ( ! is_null($transaction)) 
		{
			switch ($transaction->status->alias) 
			{
				case 'waiting_payment':
					if ($subscription->gateway_status == 'unpaid')
						return 'Aguardando pagamento';
					if ($subscription->gateway_status == 'trialing' && $badge->active)
						return 'Em trial';
					if ($subscription->gateway_status == 'canceled' && ! $badge->active)
						return 'Não aprovado.';
					if ($subscription->gateway_status == 'canceled' && $badge->active)
						return 'Cancelado.';
					break;

				case 'paid':
					if ($subscription->gateway_status == 'paid' && $badge->active)
						return 'Ativo';
					if ($subscription->gateway_status == 'trialing' && $badge->active)
						return 'Ativo';
					break;

				case 'unpaid':
					if ($badge->active)
						return 'Pagamento pendente';
					break;

				default:
					if ($subscription->gateway_status == 'unpaid')
						return 'Pagamento recusado';
					break;
			}

			return 'Em aprovação';
		} 
		else 
		{
			if ($subscription->gateway_status == 'trialing' && $badge->active)
				return 'Em trial';
			else
				return 'Em aprovação';
		}		
    }


    /**
     * Cria um review para o crachá
     * 
     */
    public function createReview(Request $request, $id){

        $badge = Badge::find($id);

        if (is_null($badge)){
            return response()->json([
                'msg' => 'Você não pode avaliar esse crachá!'
            ], 400); 
        }

        if ($badge->customer_id == $request->user()->id) {
            return response()->json([
                'msg' => 'Você não pode avaliar o seu próprio crachá!'
            ], 400);   
        }

        $duplicatedReview = $this->findDuplicated($request->user(), $badge, $request->ip);

        if (!is_null($duplicatedReview)){
            return response()->json([
                'msg' => 'Você já avaliou esse crachá!'
            ], 400);
        }
        

        $review = new BadgeReview;
        $review->reviewer_customer_id = $request->user()->id;
        $review->customer_id          = $badge->customer_id;
        $review->badge_id             = $badge->id;
        $review->quality              = $request->quality; 
        $review->punctuality          = $request->punctuality;
        $review->attendance           = $request->attendance;
        $review->average              = round( ($request->quality + $request->punctuality + $request->attendance) / 3 );
        $review->approved             = (isset($request->comment)) ? 0 : 1;
        $review->comment              = (isset($request->comment)) ? $request->comment : '';
        $review->ip                   = $request->ip;
        $review->comment              = '';

        $review->save();

        return response()->json([
            'data' => $review
        ], 201);

    }


    /**
     * Encontra reviews duplicados caso haja
     * 
	 */
	public function findDuplicated($reviewerCustomer, $badge, $ip) 
	{
		return BadgeReview::where(function($enclosure) use($badge, $reviewerCustomer) {
			$enclosure->where('badge_id', $badge->id)->where('reviewer_customer_id', $reviewerCustomer->id);
		})->orWhere(function($enclosure) use($badge, $ip) {
			$enclosure->where('badge_id', $badge->id)->where('ip', $ip);
		})->first();
    }
    
    /**
     * Retorna todos os reviews solicitados do crachá solicitado como parâmetro
     * 
     */
    public function getReviews($id)
    {
        $reviews = BadgeReview::where([
                ['badge_id', '=', $id],
                ['comment', '!=', ''],
            ])
            ->approved()
            ->get();  
        
        if($reviews->isEmpty()){
            return response()->json([
                'msg' => 'Esse crachá não tem nenhum review!'
            ], 204); 
        }

        $reviews = $this->reviewsMessage($reviews);

        foreach ($reviews as $review) {
            $review->recommended_percentage = $review->average * 20;
        }
            
        return $reviews;
    }

    /**
     * 
     */
    public function reviewsMessage($reviews){
        foreach ($reviews as $review) {
            $now = new \DateTime();
            $diff = date_diff($now, $review->updated_at)->format('%a');	
            
            if ($diff == 0) {
                $review->reviewLabel = ' Hoje';
            }elseif ($diff == 1) {
                $review->reviewLabel = ' Ontem';
            }elseif ($diff > 1 && $diff < 7) {
                $review->reviewLabel = ' Há ' . $diff . ' dias atrás';
            }elseif ($diff >= 7) {
                $review->reviewLabel = ' Há ' . round($diff/7) . ' semanas atrás';
            }else {
                $review->reviewLabel = '';
            }
        }
        
        return $reviews;
    }

    /**
     * 
     */
    public function uploadPhotosByFile(Request $request)
    {
        if ($request->count > 0) {
            for ($i=0; $i < $request->count; $i++) {
                $data = $request->file('file'.$i);
                $extension = explode('/', mime_content_type($data))[1];
    
                $name = uniqid();
                $filepath = '\images\badge_photos\\'.$name.'.'.$extension;

                $data = $request->file('file'.$i);
                $data = str_replace('data:image/png;base64,', '', $data);
                $data = str_replace('data:image/jpeg;base64,', '', $data);
                $data = str_replace('data:image/jpg;base64,', '', $data);
                $data = str_replace(' ', '+', $data);
                $data = base64_decode($data);
                file_put_contents(public_path($filepath), $data);
            
                if (file_exists(public_path($filepath))) {
                    $badgephoto = new BadgePhoto();
                    $badgephoto->badge_id = $request->badge_id;
                    $badgephoto->filename = $filepath;
                    $badgephoto->save();
                }
            }
        }
        return true;
    }

}
