<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /**
     * Cria a relação com a tabela de cupons_history
     */
    public function history()
    {
        return $this->hasMany('App\Coupon_History', 'coupon_id');
    }

    /**
     * Cria a relação com a tabela de planos
     */
    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }

    /**
     * Cria a relação com a tabela de planos
     */
    public function plans()
    {
        return $this->belongsToMany('App\Plan', 'coupon_plans');
    }

    /**
     * Define o escopo de ativo para os cupons
     */
    public function scopeActive($query){
        return $query->where('active', 1);        
    }
}
