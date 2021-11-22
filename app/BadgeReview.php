<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeReview extends Model
{
	use SoftDeletes;
	
    /**
	 * Badge belongs to Customer
	 * @return Customer
	*/
	public function customer()
	{
		return $this->belongsTo('Customer', 'customer_id');
	}

	/**
	 * Badge belongs to Customer
	 * @return Customer
	*/
	public function badge()
	{
		return $this->belongsTo('Badge', 'badge_id');
    }
    
    ##########
	# Scopes #
	##########
	
	/**
	 * Defines the monthlyCount() scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeMonthlyCount($query)
	{
		return $query->select([DB::raw('count(*) as total'), 'created_at'])->where('approved', 1)->groupBy(DB::raw('month(badge_reviews.created_at)'));
	}
	
	/**
	 * Defines the monthlyCount() scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 * @param integer $months
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeLastMonths($query, $months)
	{
		return $query->whereRaw('badge_reviews.created_at >= DATE_SUB(now(), INTERVAL ' . $months . ' MONTH)');
	}

	/**
	 * 
	 */
	public function scopeApproved($query){
		return $query->where('approved', 1);		
	}
}
