<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';

	/**
	 * http://laravel.com/docs/upgrade#upgrade-4.2
	 * Soft Deleting Models Now Use Traits
	 * 
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * Users that belongs to this role.
	 * 
	 * @return Collection
	 */
	public function users()
	{
		return $this->hasMany('User');
	}

	/**
	 * Permissions that belongs to this role.
	 * 
	 * @return Collection
	 */
	public function permissions()
	{
		return $this->belongsToMany('Permission', 'roles_has_permissions', 'role_id', 'permission_id');
	}
}
