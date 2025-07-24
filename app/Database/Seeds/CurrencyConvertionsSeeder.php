<?php
namespace App\Database\Seeds;
use Carbon\Carbon;
use App\Libraries\StatusUserEnum;

class CurrencyConvertionsSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$client = \Config\Services::curlrequest();

		// Fetch currencies
		$response = $client->get('https://api.frankfurter.dev/v1/currencies');
		$currencies = json_decode($response->getBody(), true);

		if (!empty($currencies)) {
			$data = [];
			foreach ($currencies as $code => $name) {
				$data[] = [
					'KODE_MATA_UANG' => $code,
					'NAMA_MATA_UANG' => $name,
					'created_at' => Carbon::now(),
					'updated_at' => Carbon::now(),
				];
			}
			$this->db->table('t_currency_conversions')->insertBatch($data);
		}

		// Fetch exchange rates to IDR
		$response = $client->get('https://api.frankfurter.dev/v1/latest?base=IDR');
		$rates = json_decode($response->getBody(), true);

		if (!empty($rates['rates'])) {
			$data = [];
			foreach ($rates['rates'] as $currency => $rate) {
				// Convert the rate to IDR (Rupiah)
				$rateInRupiah = 1 / $rate;

				$this->db->table('t_currency_conversions')
					->where('KODE_MATA_UANG', $currency)
					->update([
						'NILAI_TUKAR_KE_IDR' => $rateInRupiah,
						'updated_at' => Carbon::now(),
					]);
			}
		}
	}
}
