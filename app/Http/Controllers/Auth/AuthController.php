<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Badge;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'msg' => 'O e-mail e/ou senha informados estão inválidos.'
            ], 401);
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        
        $badges = Badge::where('customer_id', $user->id)->subscribed()->first();
        if (!$badges) {
            $badges = false;
        }else { 
            $badges = true;
        }


        switch ($user->genre) {
            case 0:
                $user->genre = ['code' => 0, 'name' => 'Feminino'];
                break;
            
            case 1:
                $user->genre = ['code' => 1, 'name' => '  Masculino'];
                break;
            
            case 2:
                $user->genre = ['code' => 2, 'name' => 'Outros'];
                break;

            default:
                $user->genre = ['code' => '', 'name' => 'Indefinido'];
                break;
        }

        unset($user->document);
        unset($user->document_photo);
        unset($user->created_at);
        unset($user->deleted_at);
        unset($user->updated_at);

        return response()->json([
            'user' => $user,
            'badges' => $badges,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
  



    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'msg' => 'Successfully logged out'
        ]);
    }

} 