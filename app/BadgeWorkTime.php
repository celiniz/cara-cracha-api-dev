<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgeWorkTime extends Model
{
    protected $table = 'badge_work_times';

    protected $fillable = [
        'day',
        'initial_time',
        'final_time',
        'badge_id'
    ];
    
	// n:1
	public function badge()
	{
		return $this->belongsTo('App\Badge', 'badge_id');
	}
}
