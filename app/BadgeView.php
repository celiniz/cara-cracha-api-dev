<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgeView extends Model
{
	protected $fillable = ['customer_id', 'badge_id'];

    // n:1
	public function customer()
	{
		return $this->belongsTo('Customer', 'App\customer_id');
	}
	
	// n:1
	public function badge()
	{
		return $this->belongsTo('Badge', 'App\badge_id');
	}
}
