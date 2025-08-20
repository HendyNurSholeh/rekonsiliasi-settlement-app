<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Models\ProsesModel;
use App\Traits\HasLogActivity;
use CodeIgniter\HTTP\Files\UploadedFile;

class RekonController extends BaseController
{
    use HasLogActivity;
    
    protected $prosesModel;
    
    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }
    
    /**
     * Halaman setup awal - pilih tanggal dan buat proses
     */
    public function index()
    {
        // Get default date from database where status = 1 using ORM
        $defaultDate = $this->prosesModel->getDefaultDate();
        
        $data = [
            'title' => 'Rekon Bi-FAST',
            'route' => 'rekon-bifast.rekap',
            'defaultDate' => $defaultDate
        ];
        
        return $this->render('rekon/bifast/rekon_bi.blade.php', $data);
    }

    public function upload()
    {
        try {
            helper(['form']);
            $db = \Config\Database::connect();
            $files = $this->request->getFiles()['xlsx_files'] ?? [];
            if (!is_array($files)) $files = [$files];

            $results = [];
            foreach ($files as $file) {
                if ($file->isValid() && $file->getExtension() === 'xlsx') {
                    try {
                        // Use PhpSpreadsheet for XLSX parsing
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                        $sheet = $spreadsheet->getActiveSheet();

                        // Get C15 for core_type
                        $coreTypeCell = preg_replace('/[^\w\d]/', '', $sheet->getCell('C15')->getValue());
                        $core_type = ($coreTypeCell === 'PDKSIDJ1') ? 'KONVEN' : 'SYARIAH';

                        $file_title = $file->getName();

                        $row = 21;
                        $imported = 0;
                        while (true) {
                            $transaction_id = trim((string)$sheet->getCell("C$row")->getValue());
                            if ($transaction_id === '') break;

                            $s_bank_code = trim((string)$sheet->getCell("F$row")->getValue());
                            $tx_type = (in_array($s_bank_code, ['PDKSIDJ1', 'SYKSIDJ1'])) ? 'debit' : 'credit';

                            $data = [
                                'transaction_id' => $transaction_id,
                                'ref_number' => trim((string)$sheet->getCell("D$row")->getValue()),
                                'tx_service_type' => trim((string)$sheet->getCell("E$row")->getValue()),
                                's_bank_code' => $s_bank_code,
                                's_acc_name' => trim((string)$sheet->getCell("G$row")->getValue()),
                                's_acc_number' => trim((string)$sheet->getCell("H$row")->getValue()),
                                'r_bank_code' => trim((string)$sheet->getCell("I$row")->getValue()),
                                'r_acc_name' => trim((string)$sheet->getCell("J$row")->getValue()),
                                'r_acc_number' => trim((string)$sheet->getCell("K$row")->getValue()),
                                'amount' => trim((string)$sheet->getCell("L$row")->getValue()),
                                'purpose_tx' => trim((string)$sheet->getCell("M$row")->getValue()),
                                'initiation_date' => trim((string)$sheet->getCell("N$row")->getValue()),
                                'completed_date' => trim((string)$sheet->getCell("O$row")->getValue()),
                                'desc' => trim((string)$sheet->getCell("P$row")->getValue()),
                                'status' => trim((string)$sheet->getCell("Q$row")->getValue()),
                                'status_code' => trim((string)$sheet->getCell("R$row")->getValue()),
                                'additional' => trim((string)$sheet->getCell("S$row")->getValue()),
                                'tx_type' => $tx_type,
                                'core_type' => $core_type,
                                'file_title' => $file_title,
                            ];
                            $db->table('t_ct_bifast')->insert($data);
                            $imported++;
                            $row++;
                        }
                        $results[] = "File <b>{$file->getName()}</b>: <b>$imported</b> rows imported.";
                    } catch (\Throwable $e) {
                        $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>Error: {$e->getMessage()}</span>";
                    }
                } else {
                    $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>Invalid file.</span>";
                }
            }
            return $this->response->setJSON([
                'status' => 'ok',
                'messages' => $results
            ]);
        } catch (\Throwable $ex) {
            // Return error as JSON for AJAX
            return $this->response->setJSON([
                'status' => 'error',
                'messages' => ["<span class='text-danger'>Server error: " . $ex->getMessage() . "</span>"]
            ])->setStatusCode(500);
        }
    }

    public function getCsrfToken()
    {
        return $this->response->setJSON([
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

}