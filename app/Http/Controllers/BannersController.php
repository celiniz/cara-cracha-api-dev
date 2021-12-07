<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banner;
use Illuminate\Routing\UrlGenerator;

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
        $banners = $this->banner->mobile()->orderBy('order')->get();
        $urlSite = url()->to('/');
        
        foreach($banners as $row){
            if (isset($row) && isset($row->img)) {
                $row->img = $urlSite.$row->img;
            }
        }

        return response()->json($banners, 200);        
    }
}
