<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class badge extends Model
{
	use SoftDeletes;
	
    /**
	 * Badge belongs to Category
	 * @return BadgeCategory
	*/
	public function category()
	{
		return $this->belongsTo('App\BadgeCategory', 'category_id')->withTrashed();
	}

	/**
	 * Badge belongs to Customer
	 * @return Customer
	*/
	public function customer()
	{
		return $this->belongsTo('App\User', 'customer_id');
	}

	/**
	 * Badge belongs to City
	 * @return City
	*/
	public function city()
	{
		return $this->belongsTo('App\City', 'city_id');
	}

	/**
	 * Badge belongs to Plan
	 * @return Plan
	*/
	public function plan()
	{
		return $this->belongsTo('App\Plan', 'plan_id');
	}

	/**
	 * Subscription 
	 *
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @return Subscription
	 */
	public function subscription()
	{
		return $this->belongsTo('App\Subscription');
	}

	/**
	 * Has many reviews
	 * @return BadgeReview
	*/
	public function reviews()
	{
		return $this->hasMany('App\BadgeReview', 'badge_id');
	}

	/**
	 * Has many views
	 * @return BadgeView
	*/
	public function views()
	{
		return $this->hasMany('App\BadgeView', 'badge_id');
	}

	/**
	 * Has many clicks
	 * @return BadgeClick
	*/
	public function clicks()
	{
		return $this->hasMany('App\BadgeClick', 'badge_id');
	}

	/**
	 * Has many transactions
	 * @return BadgeTransaction
	*/
	public function transactions()
	{
		return $this->hasMany('App\Transaction', 'badge_id');
	}

	/**
	 * Has many photos
	 * @return BadgePhoto
	*/
	public function photos()
	{
		return $this->hasMany('App\BadgePhoto', 'badge_id');
	}

	/**
	 * Has many WorkTime
	 * @return WorkTime
	*/
	public function workTime()
	{
		return $this->hasMany('App\BadgeWorkTime', 'badge_id');
	}

	/**
	 * Defines the subscribed() scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSubscribed($query)
	{
		return $query->whereNotNull('subscription_id');
	}

	/**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
	}
	
	/**
	 * Defines the filterForDisplay() scope.
	 *
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeFilterForDisplay($query)
	{
		$query->select(); // do not remove.

		$query->reviewsPercentage();

		// ?sort_by=newest
		if (Input::has('genre')) {
			$query->whereHas('customer', function ($query) {
				$query->where('genre', '=', Input::get('genre'));
			});
			//$query->where('genre', Input::get('genre'));
		}

		if (Input::has(['lat', 'lon'])) {
			if (Input::has('range') && Input::get('range') > 0) {
				// ?zipcode=&range=
				$query->withinRange(Input::get('lat'), Input::get('lon'), Input::get('range'));
			} else {
				// ?zipcode=
				$query->Zipcode(Input::get('lat'), Input::get('lon'));
			}
		}

		if (Input::has('sort_by')) {
			// ?sort_by=best_rated
			if (Input::get('sort_by') == 'best_rated') {
				$query->orderBy('recommended_percentage', 'desc');
			}
			
			// ?sort_by=newest
			if (Input::get('sort_by') == 'newest') {
				$query->orderBy('created_at', 'desc');
			}

			// ?sort_by=distance
			if (Input::get('sort_by') == 'distance') {
				$query->orderBy('distance', 'asc');
			}
		}

		return $query;
	}


	/**
	 * Defines the reviewsPercentage() scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeReviewsPercentage($query)
	{
		return $query->leftJoin(DB::raw('(select badge_id, round((sum(average)*20)/count(*)) as recommended_percentage from badge_reviews where approved=1 group by badge_id) as badge_reviews'), 'badge_reviews.badge_id', '=', 'badges.id');
	}


	/**
	 * Defines the withinRange($latitude, $longitude, $range) scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 * @param integer $latitude
	 * @param integer $longitude
	 * @param integer $range
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWithinRange($query, $latitude, $longitude, $range)
	{
		return $query->whereRaw("floor(6371 * acos(cos(radians({$latitude})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$longitude})) + sin(radians({$latitude})) * sin(radians(latitude)))) < {$range}");
	}

	/**
	 * Defines the zipcode($zipcode) scope.
	 * 
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @param Illuminate\Database\Eloquent\Builder $query
	 * @param integer $zipcode
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function scopeZipcode($query, $latitude, $longitude)
	{
		return $query->whereRaw("floor(6371 * acos(cos(radians({$latitude})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$longitude})) + sin(radians({$latitude})) * sin(radians(latitude)))) < badges.range");
	}

	/**
	 * 
	 * 
	 */
	public function scopeAfterToday($query){
		return $query->where('date', '>', date('Y-m-d'));		
	}
}
