<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'badge_transactions';

	protected $dates = ['created_at', 'updated_at', 'deleted_at', 'billet_expiration_date'];

	/**
	 * Transaction that belongs to a Badge.
	 * 
	 * @return Collection
	*/
	public function badge()
	{
		return $this->belongsTo('App\Badge', 'badge_id');
	}

	/**
	 * Transaction belongs to a Plan
	*/
	public function plan()
	{
		return $this->belongsTo('App\Plan', 'plan_id');
	}

	/**
	 * Transaction belongs to a Status
	*/
	public function status()
	{
		return $this->belongsTo('App\TransactionStatus', 'status_id');
	}

	/**
	 * Transaction belongs to a Status
	*/
	public function statusPivot()
	{
		return $this->belongsToMany('App\TransactionStatus', 'transaction_has_status', 'transaction_id', 'status_id')->withPivot(['created_at', 'created_by']);
	}

	/**
	 * Transaction belongs to a payment method
	*/
	public function paymentMethod()
	{
		return $this->belongsTo('App\PaymentMethod', 'payment_method_id');
	}

	/**
	 * Transaction belongs to a subscription
	*/
	public function subscription()
	{
		return $this->belongsTo('App\Subscription', 'subscription_id');
    }
    
    /**
	 * Retrieve the amount of the transaction
	 * @return Double
	*/
	public function getTotal()
	{
		return $this->amount;
	}	

	/**
	 * Retrieve the formated amount of the transaction
	 * @return String
	*/
	public function getFormatedTotal()
	{
		return 'R$ ' . number_format($this->getTotal(), 2, ',', '.');
	}	

	/**
	 * Get installments
	 * @return String
	*/
	public function getInstallments()
	{
		return $this->installments ? $this->installments . 'x' : '1x';
	}

	/**
	 * Retrieve the billet url
	 * @return String 
	*/
	public function getBilletUrl()
	{
		return $this->billet_url;
	}

	/**
	 * Get installments
	 * @return String
	*/
	public function getBilletButton()
	{
		if ($this->paymentMethod->alias == 'billet')
			return '<a href="' . $this->billet_url . '" target="_blank">' . $this->billet_barcode . '</a>';
		else
			return 'Não possui';
	}

	/**
	 * return if the column is billet
	 * @return Boolean 
	*/
	public function isBillet()
	{
		return $this->billet_url ? true : false;
	}
}
