<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKerja extends Model
{
	use SoftDeletes;

	protected $table = 'unit_kerjas'; // Make sure this matches your DB table name
	protected $primaryKey = 'id';

	protected $fillable = [
		'kode',
		'kode_dept',
		'kode_t24',
		'level',
		'type',
		'name',
		'synonym',
		'address',
		'telp'
	];

	public function scopeCabang($query)
	{
		return $query->where('type', 'cabang');
	}

	public function scopeDivisi($query)
	{
		return $query->where('type', 'divisi');
	}

	public function user()
	{
		return $this->hasMany(User::class, 'kode_unit_kerja', 'kode_t24');
	}
}
