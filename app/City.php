<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
  public $timestamps = false;
	
  protected $fillable = [
		'id',
		'uf',
		'name',
		'street',
		'district',
		'zipcode',
		'latitude',
		'longitude'
	];

	/**
	 * Package belongs to country
	 *
	 * @return Collection
	 */
	public function state()
	{
		return $this->belongsTo('App\State', 'uf');
	}
}
