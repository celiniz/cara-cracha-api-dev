<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangePlanLog extends Model {

    public $table = 'change_plan_log';

    /**
	 * Package hasMany Histories
	 *
	 * @return Collection
	 */
	public function badge()
	{
		return $this->belongsTo('App\Badge', 'badge_id');
	}

	/**
     * Cria a relação com a tabela de planos
     */
    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }
}