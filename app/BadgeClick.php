<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgeClick extends Model
{
	protected $fillable = ['customer_id', 'badge_id', 'type_of_click'];

    // n:1
	public function customer()
	{
		return $this->belongsTo('App\Customer', 'customer_id');
	}
	
	// n:1
	public function badge()
	{
		return $this->belongsTo('App\Badge', 'badge_id');
    }
    
    
}
