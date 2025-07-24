<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
	use SoftDeletes;

	protected $fillable =
		[
			'nomor_absen',
			'username',
			'email',
			'name',
			'kode_unit_kerja',
			'role',
			'password',
			'expired_at',
			'status',
			'login_attempt',
			'last_login',
			'password_expired',
			'reset_at',
			'temp_id',
			'created_by',
			'updated_by',
			'deleted_by'
		];

	protected $hidden = [
		'password',
	];

	protected $casts = [
		'expired_at' => 'datetime:j-m-Y H:i:s',
		'last_login' => 'datetime:j-m-Y H:i:s',
		'password_expired' => 'datetime:j-m-Y H:i:s',
		'reset_at' => 'datetime:j-m-Y H:i:s',
		'created_at' => 'datetime:j-m-Y H:i:s',
		'updated_at' => 'datetime:j-m-Y H:i:s',
	];

	public function unit_kerja()
	{
		return $this->belongsTo(UnitKerja::class, 'kode_unit_kerja', 'kode_t24');
	}

	public function roles()
	{
		return $this->belongsTo(Role::class, 'role', 'key');
	}

	public function req_update()
	{
		return $this->belongsTo(ReqUpdateUser::class, 'temp_id');
	}
}
