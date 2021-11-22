<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgeTransaction extends Model
{
    // n:1
	public function subscription()
	{
		return $this->belongsTo('App\Subscription', 'subscription_id');
	}
	
	// n:1
	public function status()
	{
		return $this->belongsTo('App\TransactionStatus', 'status_id');
	}
}
