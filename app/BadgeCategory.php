<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeCategory extends Model
{
	use SoftDeletes;

    /**
	 * Parent category.
	 */
	public function parent()
	{
		return $this->belongsTo('App\BadgeCategory', 'parent_id');
	}

	/**
	 * Children categories.
	 */
	public function children()
	{
		return $this->hasMany('App\BadgeCategory', 'parent_id');
	}

	/**
	 * This category's badges.
	 */
	public function badges()
	{
		return $this->hasMany('App\Badge', 'category_id');
    }
    
    ##########
	# Scopes #
	##########

	public function scopeActive($query)
	{
		return $query->where('active', 1);
	}

	public function scopeParents($query)
	{
		return $query->whereNull('parent_id');
	}
}
