<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
	use SoftDeletes;
    /**
	 * Plan has many badges
	*/
	public function badges()
	{
		return $this->belongsTo('App\Badge', 'badge_id');
    }
    
    /**
	 * Get the amount.
	 * 
	 * @return double
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Get the formated amount.
	 * 
	 * @return string
	 */
	public function getFormatedAmount()
	{
		return number_format($this->getAmount(), 2, ',', '.');
	}

	/**
	 * Get the formated description.
	 *
	 * @old getType
	 * 
	 * @return array
	 */
	public function getFormatedDays() 
	{
		switch ($this->getOriginal('days')) {
			case 7:
				$prefix = 'toda';

				$description = 'semana';

				$from_description = 'semanal';
			break;

			case 15:
				$prefix = 'toda';

				$description = 'quinzena';

				$from_description = 'quinzenal';
			break;

			case 30:
				$prefix = 'todo';

				$description = 'mês';

				$from_description = 'mensal';
			break;

			case 365:
				$prefix = 'todo';

				$description = 'ano';

				$from_description = 'anual';
			break;

			default:
				$prefix = 'cada';

				$description = $this->days . ' dias';

				$from_description = 'cada ' . $this->days . ' dias';
			break;
		}

		return [$prefix, $description, $from_description];
	}

	/**
	 * Display the amount of plan with the type. 
	 * E.g: R$9,80 / mês
	 * @return String
	*/
	public function getFormatedAmountWithType()
	{
		list(,,$from) = $this->getFormatedDays();

		return $this->getFormatedAmount() . ' / ' . $from;
	}

	public function isMostUsed()
	{
		return $this->most_used;
	}

	/**
	 * Check wether or not the plan has a trial period setup.
	 *
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @return boolean
	 */
	public function hasTrial()
	{
		return $this->trial_days > 0;
	}

	/**
	 * Get the trial days of a plan.
	 *
	 * @author Welder Lourenço <welder.lourenco@bubb.com.br>
	 * 
	 * @return boolean
	 */
	public function getTrialDays()
	{
		return $this->hasTrial() ? $this->trial_days : 0; 
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
	 * 
	 */
	public function coupons(){
		return $this->hasMany('App\Coupon', 'plan_id');
	}
}
