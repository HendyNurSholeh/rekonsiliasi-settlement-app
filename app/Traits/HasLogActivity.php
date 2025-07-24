<?php

namespace App\Traits;

use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\LogActivity;

trait HasLogActivity
{
	public $causer_id, $causer_name, $ip_address;

	public function getSession()
	{
		$this->causer_id = session('logged_in') ? session('username') : '-';
		$this->causer_name = session('logged_in') ? session('name') : '-';
		$this->ip_address = $this->request->getIPAddress();
	}

	public function logActivity(array $data)
	{
		$this->getSession();

		if (isset($data['id'])) {
			$logActivity = LogActivity::find($data['id']);
			$logActivity->update([
				'log_name' => $data['log_name'],
				'description' => $data['description'],
				'event' => $data['event'],
				'subject' => $data['subject'],
				'causer_id' => isset($data['causer_id']) ? $data['causer_id'] : $this->causer_id,
				'causer_name' => isset($data['causer_name']) ? $data['causer_name'] : $this->causer_name,
				'ip_address' => isset($data['ip_address']) ? $data['ip_address'] : $this->ip_address,
				'properties' => $data['properties'] ?? json_encode([]),
			]);
		} else {
			$logActivity = LogActivity::create([
				'log_name' => $data['log_name'],
				'description' => $data['description'],
				'event' => $data['event'],
				'subject' => $data['subject'],
				'causer_id' => isset($data['causer_id']) ? $data['causer_id'] : $this->causer_id,
				'causer_name' => isset($data['causer_name']) ? $data['causer_name'] : $this->causer_name,
				'ip_address' => isset($data['ip_address']) ? $data['ip_address'] : $this->ip_address,
				'properties' => $data['properties'] ?? json_encode([]),
			]);
		}

		return $logActivity->id;
	}

	/**
	 * Helper methods for logging specific CRUD activities.
	 */
	public function logCreated($subject, $properties = [])
	{
		return $this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Insert Data ' . $subject,
			'event' => EventLogEnum::CREATED,
			'subject' => $subject,
			'properties' => json_encode($properties),
		]);
	}

	public function logUpdated($subject, $properties = [])
	{
		return $this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data ' . $subject,
			'event' => 'update',
			'subject' => $subject,
			'properties' => json_encode($properties),
		]);
	}

	public function logDeleted($subject, $properties = [])
	{
		return $this->logActivity([
			'log_name' => 'deleted',
			'description' => 'Delete Data ' . $subject,
			'event' => 'delete',
			'subject' => $subject,
			'properties' => json_encode($properties),
		]);
	}
}
