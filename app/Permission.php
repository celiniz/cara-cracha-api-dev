<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'permissions';

	/**
	 * http://laravel.com/docs/upgrade#upgrade-4.2
	 * Soft Deleting Models Now Use Traits
	 * 
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * Roles that belongs to this permission.
	 * 
	 * @return Collection
	 */
	public function roles()
	{
		return $this->belongsToMany('Role', 'roles_has_permissions', 'permission_id', 'role_id');
	}
}
