<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CategoryNotFound;
use App\Category;

class CategoriesController extends Controller
{
    /**
     * Função que traz as categorias em destaque
     */

    public function featured(){
        $data = Category::where(["featured" => true, "active" => 1])->get();

        if ($data->isEmpty()) {
            $return = [
                'msg' => 'Não existe nenhuma categoria em destaque no momento!'
            ];
        }else {
            $return = [
                'data' => $data
            ];
        }

        return response()->json($return, 200);
    }


    /**
     * Função que retorna as categorias ativas do sistema
     */
    
    public function categories(){
        $data = Category::select(['id', 'parent_id', 'active', 'featured', 'name', 'slug'])->where("active", 1)->get();

        if ($data->isEmpty()) {
            $return = [
                'msg' => 'Não existe nenhuma categoria ativa no momento!'
            ];
        }else {
            $return = [
                'data' => $data
            ];
        }
        
        return response()->json($return, 200);
    }

    public function notfound(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|string'
        ]);

        $NotFound = new CategoryNotFound([
			'name' => $request->name,
            'email' => $request->email,
            // Se a Origem for 1 é pesquisa se for 2 é no cadastro.
			'origin' => 1,
			'status' => 1
		]);	
		
		$NotFound->save();

		return response()->json(['msg' => 'Profissão cadastrada para moderação!'], 200);                
    }
    
}
