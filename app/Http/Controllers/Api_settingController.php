<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Api_setting;

class Api_settingController extends Controller
{

    /**
     * 
     * Retorna todos os dados do banco na tabela Api Setting
     * 
     */
    public function get(){
        return response()->json(['data' => Api_setting::all()], 200);     
    }
}
