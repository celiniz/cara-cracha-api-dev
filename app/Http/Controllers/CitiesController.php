<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\CityRepository;

class CitiesController extends Controller
{
  private $city;
  public function __construct(CityRepository $city){
    $this->city = $city;    
  }

  /**
   * Função que retorna a cidade conforme parâmetro passado pela request
   */
  
  public function getCity(Request $request)
  {
    $city = $this->city->getCity($request);
    
    return response()->json($city, 200);
  }
}
