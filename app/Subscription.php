<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	protected $fillable = ['plan_id', 'gateway_id', 'gateway_status', 'start_at', 'end_at'];

    /**
	 * http://laravel.com/docs/upgrade#upgrade-4.2
	 * Soft Deleting Models Now Use Traits
	 * 
	 * @var array
	*/
	protected $dates = ['start_at', 'end_at', 'created_at', 'updated_at'];

	/**
	 * Subscription belongs to plan
	*/
	public function plan()
	{
		return $this->belongsTo('App\Plan', 'plan_id');
	}

	public function transactions()
	{
		return $this->hasMany('App\BadgeTransaction', 'subscription_id');
	}

	public function badges()
	{
		return $this->hasMany('App\Badge', 'subscription_id');
	}
}
