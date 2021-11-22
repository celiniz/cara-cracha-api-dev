<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

	/**
	 * ?
	 */
	public function scopeFindByAlias($query, $alias)
	{
		return $query->where('alias', $alias)->firstOrFail();
	}
}
