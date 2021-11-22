<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
		'uf',
		'name'
	];

	public $timestamps = false;

	protected $primaryKey = 'uf';

	/**
	 * Package belongs to country
	 *
	 * @return Collection
	*/
	public function cities()
	{
		return $this->hasMany('App\City');
	}
}
