<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCreditCard extends Model
{
    protected $fillable = [
		'customer_id',
		'card_id',
		'brand',
		'first_digits',
		'last_digits'
	];

	/**
	 * Package belongs to country
	 *
	 * @return Collection
	 */
	public function customer()
	{
		return $this->belongsTo('CustomerCreditCard', 'customer_id');
    }
    
    /**
	 * Retrieve the font awesome icon name from the current brand
	 * @return String
	*/
	public function getFaIcon()
	{
		$faIcon = 'fa-cc';
		
		if (in_array($this->brand, ['visa', 'mastercard', 'amex']))
			$faIcon .=  '-' . $this->brand;

		return $faIcon;
	}

	/**
	 * Retrieve the display name of credit card brand
	 * @return String
	*/
	public function getBrandLabel()
	{
		switch ($this->brand)
		{
			case 'mastercard':
				$label = "Master card";
			break;
			case 'amex':
				$label = 'American Express';
			break;
			default: 
				$label = ucfirst($this->brand);
			break;
		}

		return $label;
	}
}
