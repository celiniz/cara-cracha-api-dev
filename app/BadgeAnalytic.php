<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgeAnalytic extends Model
{
    /**
	 * BadgeAnalyttic belongs to BadgeAnalyttic
	 * @return Badge
	*/
	public function badge()
	{
		return $this->belongsTo('Badge', 'badge_id');
	}
}
