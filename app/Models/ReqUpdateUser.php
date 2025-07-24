<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReqUpdateUser extends Model
{
	protected $fillable =
		[
			'user_id',
			'original_field',
			'updated_field',
			'status',
			'category',
			'applicant',
			'applicant_id',
			'approval',
			'approval_id',
			'returner',
			'returner_id',
			'cabang',
			'filename',
			'base64',
			'note',
			'permission',
			'approved_at',
			'return_at',
		];

	protected $casts = [
		'created_at' => 'datetime:j-m-Y H:i:s',
	];

	public function user()
	{
		return $this->hasOne(User::class, 'temp_id');
	}
}
