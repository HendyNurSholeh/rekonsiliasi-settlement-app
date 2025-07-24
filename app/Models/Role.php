<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
	use SoftDeletes;

	protected $fillable =
		[
			'key',
			'name',
		];

	public function user()
	{
		return $this->hasMany(User::class, 'role', 'key');
	}

	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
	}
}
