<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryNotFound extends Model
{
    public $table = 'categories_not_found';
    
    protected $fillable = array('name', 'email', 'origin', 'status');


    /**
     * Define o escopo de ativo para os cupons
     */
    public function scopeActive($query){
        return $query->where('status', 1);        
    }
}
