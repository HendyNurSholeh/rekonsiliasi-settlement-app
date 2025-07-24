<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use Config\Services;
use App\Models\Token;
use App\Libraries\LogEnum;
use App\Libraries\EventLogEnum;

trait HasCurlRequest
{
	use HasLogActivity;

	public $endpoint;
	public $user;
	public $secret;

	public function initialize()
	{
		$this->endpoint = env('curl.endpoint');
	}

	// public function getToken()
	// {
	// 	$this->initialize();

	// 	$validToken = Token::where('expired_at', '>', date('Y-m-d H:i:s'))->first();
	// 	if ($validToken) {
	// 		return $validToken->token;
	// 	}

	// 	$slug = 'authentication';
	// 	$url = $this->endpoint . $slug;
	// 	$params = [
	// 		'user' => $this->user,
	// 		'secret' => $this->secret,
	// 	];

	// 	$id = $this->logActivity([
	// 		'log_name' => LogEnum::API,
	// 		'description' => 'Get Token',
	// 		'event' => EventLogEnum::PENDING,
	// 		'subject' => $url,
	// 		'properties' => json_encode($params)
	// 	]);

	// 	$headers = [
	// 		'Accept' => 'application/json',
	// 	];
	// 	$response = $this->sendRequest('POST', $url, ['headers' => $headers, 'body' => json_encode($params)]);

	// 	$this->logActivity([
	// 		'id' => $id,
	// 		'log_name' => LogEnum::API,
	// 		'description' => 'Get Token',
	// 		'event' => EventLogEnum::VERIFIED,
	// 		'subject' => $url,
	// 		'properties' => json_encode([
	// 			'request' => $params,
	// 			'response' => $response
	// 		])
	// 	]);

	// 	if ($response['code'] !== 'SUCCESS') {
	// 		$response['error'] = true;
	// 		return $response;
	// 	}

	// 	$token = $response['data']['accessToken'];
	// 	$expired_at = $response['data']['expiry'];
	// 	$expired_at = Carbon::parse($expired_at)->format('Y-m-d H:i:s');

	// 	Token::create([
	// 		'token' => $token,
	// 		'expired_at' => $expired_at
	// 	]);

	// 	return $token;
	// }

	// public function loginRequest($slug, $data, $method = 'GET')
	// {
	// 	$token = $this->getToken();
	// 	if (isset($token['error'])) {
	// 		return $token;
	// 	}

	// 	$url = $this->endpoint . $slug;

	// 	$headers = [
	// 		'Authorization' => 'Bearer ' . $token,
	// 		'Accept' => 'application/json',
	// 	];

	// 	$id = $this->logActivity([
	// 		'log_name' => LogEnum::AUTH,
	// 		'description' => 'Login Request',
	// 		'event' => EventLogEnum::PENDING,
	// 		'subject' => $url,
	// 		'properties' => json_encode($data)
	// 	]);

	// 	$response = $this->sendRequest($method, $url, ['headers' => $headers, 'body' => json_encode($data)]);

	// 	$this->logActivity([
	// 		'id' => $id,
	// 		'log_name' => LogEnum::API,
	// 		'description' => 'Login Request',
	// 		'event' => EventLogEnum::VERIFIED,
	// 		'subject' => $url,
	// 		'properties' => json_encode([
	// 			'request' => $data,
	// 			'response' => $response
	// 		])
	// 	]);

	// 	return $response;
	// }

	// public function showData($slug, $data = [], $method = 'GET', $desc = null)
	// {
	// 	$token = $this->getToken();
	// 	if (isset($token['error'])) {
	// 		return $token;
	// 	}

	// 	$url = $this->endpoint . $slug;

	// 	$headers = [
	// 		'Authorization' => 'Bearer ' . $token,
	// 		'Accept' => 'application/json',
	// 	];

	// 	$id = $this->logActivity([
	// 		'log_name' => LogEnum::API,
	// 		'description' => $desc ?? 'Get Data',
	// 		'event' => EventLogEnum::PENDING,
	// 		'subject' => $url,
	// 		'properties' => json_encode($data)
	// 	]);

	// 	$response = $this->sendRequest($method, $url, ['headers' => $headers, 'body' => json_encode($data)]);

	// 	$this->logActivity([
	// 		'id' => $id,
	// 		'log_name' => LogEnum::API,
	// 		'description' => $desc ?? 'Get Data',
	// 		'event' => EventLogEnum::VERIFIED,
	// 		'subject' => $url,
	// 		'properties' => json_encode([
	// 			'request' => $data,
	// 			'response' => $response
	// 		])
	// 	]);

	// 	return $response;
	// }

	public function postData($url, $data)
	{
		// $token = $this->getToken();

		$headers = [
			// 'Authorization' => 'Bearer ' . $token,
			'Accept' => 'application/json',
		];

		$response = $this->sendRequest('POST', $url, ['headers' => $headers, 'body' => json_encode($data)]);
		return $response;
	}

	public function insertData($slug, $data, $desc = null)
	{
		$this->initialize();
		$url = $this->endpoint . $slug;

		$id = $this->logActivity([
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Insert Data',
			'event' => EventLogEnum::PENDING,
			'subject' => $url,
			'properties' => json_encode($data)
		]);

		$result = $this->postData($url, $data);

		$this->logActivity([
			'id' => $id,
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Insert Data',
			'event' => EventLogEnum::VERIFIED,
			'subject' => $url,
			'properties' => json_encode([
				'request' => $data,
				'response' => $result
			])
		]);

		return $result;
	}

	public function updateData($slug, $data, $desc = null)
	{
		$this->initialize();
		$url = $this->endpoint . $slug;

		$id = $this->logActivity([
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Update Data',
			'event' => EventLogEnum::PENDING,
			'subject' => $url,
			'properties' => json_encode($data)
		]);

		$result = $this->postData($url, $data);

		$this->logActivity([
			'id' => $id,
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Update Data',
			'event' => EventLogEnum::VERIFIED,
			'subject' => $url,
			'properties' => json_encode([
				'request' => $data,
				'response' => $result
			])
		]);

		return $result;
	}

	public function deleteData($slug, $data, $desc = null)
	{
		$this->initialize();
		$url = $this->endpoint . $slug;

		$id = $this->logActivity([
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Delete Data',
			'event' => EventLogEnum::PENDING,
			'subject' => $url,
			'properties' => json_encode($data)
		]);

		$result = $this->postData($url, $data);

		$this->logActivity([
			'id' => $id,
			'log_name' => LogEnum::API,
			'description' => $desc ?? 'Delete Data',
			'event' => EventLogEnum::VERIFIED,
			'subject' => $url,
			'properties' => json_encode([
				'request' => $data,
				'response' => $result
			])
		]);

		return $result;
	}

	private function sendRequest($method, $url, $options = [])
	{
		$options = array_merge($options, ['http_errors' => false]);
		// print_r($options); die;

		$curl = Services::curlrequest();
		$response = $curl->request($method, $url, $options);

		return json_decode($response->getBody(), true);
	}
}
