<?php 

namespace App\Repositories;

use Illuminate\Http\Request;
use App\User;
use App\BadgeTransaction;
use Illuminate\Support\Collection;

class TransactionRepository
{
	public function getAllFromCustomer($customerId)
	{
		$invoices = [];

		foreach (User::with(['badges'])->find($customerId)->badges as $badge) 
		{
			$transactions = BadgeTransaction::where('subscription_id', $badge->subscription)->get();
			foreach ($transactions as $transaction)
			{
				$transaction->status_name = $transaction->status->name;
				$transaction->badge_nickname = $badge->nickname;
                $invoices[] = $transaction;
			}
		}
		
		if(empty($invoices)){
			return response()->json([
				'msg' => 'Sem transações!'
			]);
		}
        
		return Collection::make($invoices);
	}
}