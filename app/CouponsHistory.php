<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponsHistory extends Model
{
	public $table = 'coupons_history';
	
	/**
	 * Package belongs to country
	 *
	 * @return Collection
	 */
	public function coupon()
	{
		return $this->belongsTo('Coupon', 'coupon_id');
	}


	/**
	 * Package belongs to country
	 *
	 * @return Collection
	 */
	public function badge()
	{
		return $this->belongsTo('Badge', 'badge_id');
	}
}