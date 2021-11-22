<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class SettingsController extends Controller
{
    private $settings;

    public function __construct(Setting $settings){
        $this->settings = $settings;
    }

    /**
     * Retorna todas as configurações
     */

    public function getAll(){
        $setting = $this->settings->all();

        foreach ($setting as $e) {
            $e->active = boolval($e->active);
        }
        return response()->json($setting, 200);
    }

    /**
     * Retorna todas as configurações
     */

    public function getTag($tag){

        $setting = $this->settings->where('tag', $tag)->first();

        $setting->active = boolval($setting->active);

        return response()->json($setting, 200);
    }
}
