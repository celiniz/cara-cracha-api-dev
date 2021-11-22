<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /**
     * Define o escopo de ativo para os cupons
     */
    public function scopeMobile($query){
        return $query->where('device', 1);        
    }
    
}
