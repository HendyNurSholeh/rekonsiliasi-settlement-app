<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogActivity extends Model
{
	protected $fillable =
		[
			'log_name',
			'description',
			'event',
			'subject_id',
			'subject',
			'causer_id',
			'causer_name',
			'ip_address',
			'properties',
		];
}
