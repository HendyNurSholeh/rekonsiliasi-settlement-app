<?php

namespace App\Controllers\User;

use App\Models\UnitKerja;
use App\Traits\HasLogActivity;
use Carbon\Carbon;
use App\Models\Role;
use App\Libraries\LogEnum;
use App\Libraries\ActiveEnum;
use App\Traits\HasCurlRequest;
use App\Libraries\ApprovalEnum;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class UnitKerjaController extends BaseController
{
	use HasLogActivity;

	protected $helpers = ['form'];

	public function index()
	{
		if (!in_array('view unit kerja', session('permissions'))) {
			$this->response->redirect(base_url('dashboard'));
		}

		$data = [
			'title' => 'Daftar Unit Kerja',
			'route' => 'unit-kerja'
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('user/unit-kerja.blade.php', $data);
	}

	public function dataTables()
	{
		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');

		$columnIndex = $this->request->getGet('order')[0]['column'];
		$columnName = $this->request->getGet('columns')[$columnIndex]['data'];
		$columnSortOrder = $this->request->getGet('order')[0]['dir'];
		$searchValue = $this->request->getGet('search')['value'];

		$totalRecords = UnitKerja::all()
			->count();
		$totalRecordswithFilter = UnitKerja::where('kode_t24', 'like', '%' . $searchValue . '%')
			->orWhere('name', 'like', '%' . $searchValue . '%')
			->count();

		$records = UnitKerja::orderBy($columnName, $columnSortOrder)
			->where('kode_t24', 'like', '%' . $searchValue . '%')
			->orWhere('name', 'like', '%' . $searchValue . '%')
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$data = array_map(function ($item, $index) use ($start) {
			$item['no'] = $start + $index + 1;
			$item['aksi'] = "
				<div class='btn-group btn-group-sm' role='group'>
					<button type='button' class='btn btn-sm btn-primary' onclick='editModal($index)'><i class='fal fa-edit'></i></button>
					<button type='button' class='btn btn-sm btn-danger' onclick='deleteData($index)'><i class='fal fa-trash'></i></button>
				</div>
			";

			return $item;
		}, $records, array_keys($records));

		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $data
		);

		return $this->response->setJSON($response);
	}

	public function post()
	{
		$validate = $this->validate([
			'kode' => 'required|is_unique[unit_kerjas.kode]',
			'kode_dept' => 'required',
			'kode_t24' => 'required|is_unique[unit_kerjas.kode_t24]',
			'level' => 'required|numeric',
			'type' => 'required',
			'name' => 'required|max_length[255]',
			'synonym' => 'permit_empty',
			'telp' => 'permit_empty',
			'address' => 'permit_empty'
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$data = $this->request->getPost();
		unset($data['id']);
		$unit_kerja = UnitKerja::create($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Insert Data',
			'event' => EventLogEnum::CREATED,
			'subject_id' => $unit_kerja->id,
			'subject' => UnitKerja::class,
			'properties' => json_encode($data)
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil ditambahkan!',
			'token' => csrf_hash()
		]);
	}

	public function edit()
	{
		$data = $this->request->getPost();
		$id = $data['id'];
		unset($data['id']);

		$validate = $this->validate([
			'kode' => "required|is_unique[unit_kerjas.kode,id,{$id}]",
			'kode_dept' => 'required',
			'kode_t24' => "required|is_unique[unit_kerjas.kode_t24,id,{$id}]",
			'level' => 'required|numeric',
			'type' => 'required',
			'name' => 'required|max_length[255]',
			'synonym' => 'permit_empty',
			'telp' => 'permit_empty',
			'address' => 'permit_empty'
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$unit_kerja = UnitKerja::find($id);
		$old = $unit_kerja->toArray();
		$unit_kerja->update($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $unit_kerja->id,
			'subject' => UnitKerja::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $unit_kerja->toArray()
			])
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil diubah!',
			'token' => csrf_hash()
		]);
	}

	public function delete()
	{
		$id = $this->request->getPost('id');

		$unit_kerja = UnitKerja::find($id);
		$unit_kerja->delete();

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Delete Data',
			'event' => EventLogEnum::DELETED,
			'subject_id' => $unit_kerja->id,
			'subject' => UnitKerja::class,
			'properties' => json_encode($unit_kerja->toArray())
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil dihapus!',
			'token' => csrf_hash()
		]);
	}

	public function optionsCabOnly()
	{
		$kode = $this->request->getPost('kode');
		if ($kode) {
			// Find by kode (unit) - support both kode and kode_t24
			$item = UnitKerja::where('kode', $kode)->orWhere('kode_t24', $kode)->first();
			if ($item) {
				return $this->response->setJSON([
					'status' => 200,
					'data' => [
						[
							"id" => $item->kode,
							"text" => $item->kode_t24 . ' - ' . $item->name,
							"name" => $item->name,
							"kode" => $item->kode,
							"kode_t24" => $item->kode_t24
						]
					],
					'token' => csrf_hash()
				]);
			} else {
				return $this->response->setJSON([
					'status' => 404,
					'data' => [],
					'token' => csrf_hash()
				]);
			}
		}
		// Default: return all cabang
		$data = UnitKerja::cabang()->get()->toArray();

		$result = array_map(function ($item) {
			return [
				"id" => $item["kode_t24"],
				"text" => $item["kode_t24"] . ' - ' . $item["name"],
				"name" => $item["name"],
				"kode" => $item["kode"],
				"kode_t24" => $item["kode_t24"]
			];
		}, $data);

		return $this->response->setJSON([
			'status' => 200,
			'data' => $result,
			'token' => csrf_hash()
		]);
	}

	public function optionsDivOnly()
	{
		$data = Unitkerja::divisi()->get()->toArray();

		$result = array_map(function ($item) {
			return [
				"id" => $item["kode_t24"],
				"text" => $item["kode_t24"] . ' - ' . $item["name"],
			];
		}, $data);

		return $this->response->setJSON([
			'status' => 200,
			'data' => $result,
			'token' => csrf_hash()
		]);
	}

	public function options()
	{
		$data = Unitkerja::all()->toArray();

		$result = array_map(function ($item) {
			return [
				"id" => $item["kode_t24"],
				"text" => $item["kode_t24"] . ' - ' . $item["name"],
			];
		}, $data);

		return $this->response->setJSON([
			'status' => 200,
			'data' => $result,
			'token' => csrf_hash()
		]);
	}
}
