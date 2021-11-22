<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Repositories\BadgeRepository;

class CustomerRepository
{
    private $bagde;
    public function __construct(BadgeRepository $badge){
        $this->badge = $badge;        
    }

    /**
     * Create user
     *
     * @param  [string] first_name
     * @param  [string] last_name
     * @param  [string] document
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $user = new User([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'genre' => (isset($request->genre)) ? $request->genre : null,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'code' => mt_rand(1000000, 9999999)
        ]);

        if ($user->genre == 3) {
            $user->genre = 0;            
        }
        
        $user->save();

        return $user;
    }


    /*
    * Retorna os dados do usuário para a atualização.
    *
    *
    *
    *
    *
    */
    public function update(Request $request){
        $user = $request->user();
        $user->genre = $this->getGenre($user->genre);

        return $user;       
    }



    /*
    * Salva os dados do usuário atualizados.
    *
    *
    *
    *
    *
    */
    public function updateCustomer(Request $request){

        $user = Auth::user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if (isset($request->password)) {
            $request->validate([
                'password' => 'required|string|confirmed'
            ]);

            $user->password = bcrypt($request->password);
        }

        $user->save();

        return null;        
    }

    public function getGenre($genre){
        switch ($genre) {
            case 0:
                $genre = ['code' => 0, 'name' => 'Feminino'];
                break;
            
            case 1:
                $genre = ['code' => 1, 'name' => '  Masculino'];
                break;
            
            case 2:
                $genre = ['code' => 2, 'name' => 'Outros'];
                break;

            default:
                $genre = ['code' => '', 'name' => 'Não informado'];
                break;
        }

        return $genre;
        
    }

    /**
     * 
     * 
     */
    public function checkEmail($email){
        $return = User::where('email', $email)->count();

        if ($return > 0) {
            $return = true;
        } else {
            $return = false;
        }

        return response()->json([
            'data' => $return
        ], 200);  
    }
}
