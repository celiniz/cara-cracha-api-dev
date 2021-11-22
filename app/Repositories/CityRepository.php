<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Config;
use App\City;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CityRepository
{

  public function getCity(Request $request)
  {
    if (isset($request->cep)) {
      $request->validate([
        'cep' => 'required|string',
      ]);

      return $this->getCityByCep($request->cep);
    } else {
      $request->validate([
        'latitude' => 'required|string',
        'longitude' => 'required|string'
      ]);

      return $this->getCityByLocation($request->latitude, $request->longitude);
    }
  }

  public function getCityByCep($zipcode)
  {
    if (is_null($zipcode)) $zipcode = Input::get('zipcode');

    // sanitize the zipcode.
    $zipcode = str_replace('-', '', $zipcode);

    $city = City::firstOrNew(['zipcode' => str_replace('-', '', $zipcode)]);

    if (!is_null($city->id)) return $city;

    // we do not have that zipcode. search and create it.
    try {

      $client = new Client;

      $zipcodeToSearch = substr_replace($zipcode, "-", 5, 0);

      $uniqueZipcode = (substr($zipcodeToSearch, -3) == 000) ? true : false;

      $responseByCep = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $zipcodeToSearch . '+Brasil&key=AIzaSyDAadMpr4F7KkT6OlGbykKl5ijJfNHU4jU');

      $responseByCep = json_decode($responseByCep->getBody(), true);

      if (empty($responseByCep) || $responseByCep['status'] == 'ZERO_RESULTS') return response()->json(null, 404);

      if (!$this->mapsDataCheck($responseByCep, $zipcodeToSearch)) 
      {
        return null;
      }

      if ($responseByCep['results'][0]['address_components'][0]['types'][0] != 'postal_code' || $responseByCep['results'][0]['address_components'][0]['long_name'] != $zipcodeToSearch) {
        return response()->json(null, 404);
      }

      $latitude = $responseByCep['results'][0]['geometry']['location']['lat'];
      $longtitude = $responseByCep['results'][0]['geometry']['location']['lng'];

      if (empty($latitude) || empty($longtitude)) return response()->json(null, 404);
      

      $responseByLatLng = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latitude . ',' . $longtitude . '&key=AIzaSyDAadMpr4F7KkT6OlGbykKl5ijJfNHU4jU');

      $responseByLatLng = json_decode($responseByLatLng->getBody(), true);

      $city = $this->getMapsData($responseByLatLng);

      $city->zipcode = !empty($zipcode) ? str_replace('-', '', $zipcode) : null;
      $city->latitude = !empty($latitude) ? strval($latitude) : null;
      $city->longitude = !empty($longtitude) ? strval($longtitude) : null;
      $city->save();

      return $city;
    } catch (Exception $e) {
      return response()->json(null, 404);
    }
  }


  public function getCityByLocation($lat, $lon)
  {

    $city = City::firstOrNew(['latitude' => $lat, 'longitude' => $lon]);
    
    if (!is_null($city->id)) return $city;
    

    // we do not have that zipcode. search and create it.
    try {

      $client = new Client;
      //dd('Authorization' . 'Token ' . Config::get('services.cepaberto.key'));

      $responseByLatLng = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lon . '&key=AIzaSyDAadMpr4F7KkT6OlGbykKl5ijJfNHU4jU');
      
      $responseByLatLng = json_decode($responseByLatLng->getBody(), true);

      if (empty($responseByLatLng) || $responseByLatLng['status'] == 'ZERO_RESULTS') return response()->json(null, 404);

      $city = $this->getMapsData($responseByLatLng);

      $city = $this->getMapsDataLocation($responseByLatLng, $city);
      $city->latitude = !empty($lat) ? strval($lat) : null;
      $city->longitude = !empty($lon) ? strval($lon) : null;
      $city->save();
      
      return $city;
    } catch (Exception $e) {
      return response()->json(null, 404);
    }
  }

  /**
   * Interpretação dos dados retornados pelo G Maps
   */
  public function getMapsData($data)
  {
    $city = new City;

    foreach ($data['results'][0]['address_components'] as $a) 
    {
      if (in_array('route', $a['types'])) {
        
        $city->street = $a['long_name'];
      }

      if (in_array('sublocality_level_1', $a['types'])) {
        
        $city->district = $a['long_name'];
      }

      if (in_array('administrative_area_level_2', $a['types'])) {
        
        $city->name = $a['long_name'];
      }

      if (in_array('administrative_area_level_1', $a['types'])) {
        
        $city->uf = $a['short_name'];
      }
    }

    return $city;
  }

  /**
   * Interpretação dos dados retornados pelo G Maps PARA lAT E lON
   */
  public function getMapsDataLocation($data, $city)
  {

    foreach ($data['results'][0]['address_components'] as $a) 
    {
      if (in_array('postal_code', $a['types']) || in_array('postal_code_prefix', $a['types'])) 
      {
        $city->zipcode = $a['long_name'];
      }
    }

    return $city;
  }

  /**
   * Verificação se Maps retornou o CEP
   */
  public function mapsDataCheck($data, $zipCode)
  {
    $isValid = false;
    
    foreach ($data['results'][0]['address_components'] as $a) 
    {
      if (in_array('postal_code_prefix', $a['types'])) 
      {
        if (substr($zipCode, 0, -4) == $a['long_name']) {
          $isValid = true;
        }
      }
      elseif (in_array('postal_code', $a['types'])) 
      {
        $isValid = true;
      }
    }

    return $isValid;
  }
}
