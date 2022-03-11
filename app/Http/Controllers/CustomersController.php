<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Repositories\CustomerRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;
class CustomersController extends Controller
{
    private $customer;
    public function __construct(CustomerRepository $customer){
        $this->customer = $customer;        
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
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string|email|unique:customers',
                'password' => 'required|string|confirmed'
            ]);
    
        } catch (ValidationException $exception){
            return response()->json([
                'status' => 'error',
                'msg'    => 'Error',
                'errors' => $exception->errors(),
            ], 409);
        }

        $user = $this->customer->signup($request); 
    
        return response()->json([
            'msg' => 'Successfully created user!',
            'user' => $user
        ], 201);
    }


    /*
    * Função que atualiza o usuário logado
    */
    public function update(Request $request){
        echo 1;die;
        $user = $this->customer->update($request);  

        return response()->json([
            'data' => $user
        ], 200); 
    }



    /*
    * Função que atualiza o usuário logado
    */
    public function updateCustomer(Request $request){

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ]);
        
        $this->customer->updateCustomer($request); 

        return response()->json([
            'msg' => 'Usuário alterado com sucesso!'
        ], 201);
    }

    /*
    * Função que verifica se o e-mail está cadastrado
    */
    public function email($email){
        
        return $this->customer->checkEmail($email); 
    }

    public function getFinancial(Request $request){
        $transactions = new TransactionRepository;
        return $transactions->getAllFromCustomer($request->user()->id);
                
    }
}
