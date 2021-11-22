<?php

namespace App\Http\Controllers;
use Artisan;
use Illuminate\Http\Request;

class Rotas extends Controller
{
    /**
     * Função que retorna todas as rotas do sistema
     */
    
    public function showRoutes(Request $request) {
        $routes = [];
        foreach (\Route::getRoutes()->getIterator() as $route){
            if (strpos($route->uri, 'api') !== false){
                $routes[] = $route;
            }
        }
        return view('rotas', compact('routes')); 
    }
}
