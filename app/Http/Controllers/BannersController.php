<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banner;

class BannersController extends Controller
{
    private $banner;

    public function __construct(Banner $banner){
        $this->banner = $banner;        
    }

    /**
     * Retorna o cupom com o ID passado como parâmetro
     */

    public function get(){
        return response()->json($this->banner->mobile()->orderBy('order')->get(), 200);        
    }
}
