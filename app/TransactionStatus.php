<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionStatus extends Model
{
    public $table = 'transaction_status';

    /**
	 * Find a status by an alias.
	 * 
	 * @param string $alias
	 * 
	 * @return TransactionStatus
	 */
	public function findByAlias($alias)
	{
		return TransactionStatus::where('alias', $alias)->firstOrfail();
	}

	/**
	 * Receives an external status alias and find our app status by proximity.
	 * 
	 * @param string $externalStatus
	 * 
	 * @return TransactionStatus
	 */
	public function findByBind($externalStatus) 
	{
		switch ($externalStatus)
		{
			# Pagar.Me statuses
			case 'processing':
				return $this->findByAlias('processing');
			break;

			case 'authorized':
				return $this->findByAlias('authorized');
			break;

			case 'trialing':
				return $this->findByAlias('trialing');
			break;

			case 'paid':
				return $this->findByAlias('paid');
			break;

			case 'unpaid':
				return $this->findByAlias('unpaid');
			break;

			case 'refunded':
				return $this->findByAlias('refunded');
			break;

			case 'waiting_payment':
				return $this->findByAlias('waiting_payment');
			break;

			case 'pending_refund':
				return $this->findByAlias('waiting_refund');
			break;

			case 'refused':
				return $this->findByAlias('refused');
			break;

			case 'chargedback':
				return $this->findByAlias('chargedback');
			break;

			case 'canceled':
				return $this->findByAlias('canceled');
			break;

			default:
				throw new Exception('Status ' . $externalStatus . ' not binded.');
			break;
		}
	}
}
