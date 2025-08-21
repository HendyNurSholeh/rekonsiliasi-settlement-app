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
        
        $db = \Config\Database::connect();

        // Pagination and sorting for AJAX
        $perPage = 10;
        $page = (int)($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;
        $sortField = $this->request->getGet('sort');
        $sortDir = strtolower($this->request->getGet('dir')) === 'asc' ? 'ASC' : 'DESC';

        $allowedSort = ['no', 'file_title', 'trx_date', 'core_type'];
        if (!$sortField || !in_array($sortField, $allowedSort)) {
            $sortField = 'file_title';
            $sortDir = 'DESC';
        }

        // Filter by date if provided
        $filterDate = $this->request->getGet('filter_date');
        $builder = $db->table('v_ct')
            ->select('file_title, trx_date, core_type');
        if ($filterDate) {
            $builder->where('DATE(trx_date)', $filterDate);
        }

        // Use natural sort for file_title
        if ($sortField === 'file_title') {
            $builder->orderBy("LENGTH(file_title)", $sortDir)
                    ->orderBy("file_title", $sortDir);
        } else {
            $builder->orderBy($sortField === 'no' ? 'trx_date' : $sortField, $sortDir);
        }

        // AJAX for XLSX table
        if ($this->request->getGet('ajax')) {
            $total = $builder->countAllResults(false);
            $files = $builder->limit($perPage, $offset)->get()->getResultArray();
            $data = [];
            foreach ($files as $i => $file) {
                $data[] = [
                    'no' => $offset + $i + 1,
                    'file_title' => $file['file_title'],
                    'trx_date' => $file['trx_date'],
                    'core_type' => $file['core_type'],
                ];
            }
            return $this->response->setJSON([
                'data' => $data,
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $page,
            ]);
        }

        // AJAX for CSV table
        if ($this->request->getGet('ajax_memstat')) {
            $perPageCsv = 10;
            $pageCsv = (int)($this->request->getGet('page') ?? 1);
            $offsetCsv = ($pageCsv - 1) * $perPageCsv;
            $sortFieldCsv = $this->request->getGet('sort') ?? 'file_name';
            $sortDirCsv = strtolower($this->request->getGet('dir')) === 'asc' ? 'ASC' : 'DESC';
            $allowedSortCsv = ['no', 'file_name', 'trx_date', 'unique_file'];
            if (!in_array($sortFieldCsv, $allowedSortCsv)) $sortFieldCsv = 'file_name';

            $filterDateCsv = $this->request->getGet('filter_date');
            $builderCsv = $db->table('v_memstat')
                ->select('file_name, trx_date, unique_file');
            if ($filterDateCsv) {
                $builderCsv->where('DATE(trx_date)', $filterDateCsv);
            }
            if ($sortFieldCsv === 'file_name') {
                $builderCsv->orderBy("LENGTH(file_name)", $sortDirCsv)
                           ->orderBy("file_name", $sortDirCsv);
            } else {
                $builderCsv->orderBy($sortFieldCsv === 'no' ? 'trx_date' : $sortFieldCsv, $sortDirCsv);
            }
            $totalCsv = $builderCsv->countAllResults(false);
            $filesCsv = $builderCsv->limit($perPageCsv, $offsetCsv)->get()->getResultArray();
            $dataCsv = [];
            foreach ($filesCsv as $i => $file) {
                $dataCsv[] = [
                    'no' => $offsetCsv + $i + 1,
                    'file_name' => $file['file_name'],
                    'trx_date' => $file['trx_date'],
                    'unique_file' => $file['unique_file'],
                ];
            }
            return $this->response->setJSON([
                'data' => $dataCsv,
                'total' => $totalCsv,
                'perPage' => $perPageCsv,
                'currentPage' => $pageCsv,
            ]);
        }

        $uploadedFiles = $builder->limit($perPage, $offset)->get()->getResultArray();

        $data = [
            'title' => 'Rekon Bi-FAST',
            'route' => 'rekon-bifast.rekap',
            'defaultDate' => $defaultDate,
            'uploadedFiles' => $uploadedFiles,
        ];
        
        return $this->render('rekon/bifast/rekon_bi.blade.php', $data);
    }

    public function upload()
    {
        // Set max execution time to 5 minutes
        ini_set('max_execution_time', 300);

        try {
            helper(['form']);
            $db = \Config\Database::connect();
            $files = $this->request->getFiles();

            $results = [];

            // Handle XLSX files (existing logic)
            $xlsxFiles = $files['xlsx_files'] ?? [];
            if (!is_array($xlsxFiles)) $xlsxFiles = [$xlsxFiles];
            foreach ($xlsxFiles as $fileIdx => $file) {
                if ($file->isValid() && $file->getExtension() === 'xlsx') {
                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                        $sheet = $spreadsheet->getActiveSheet();

                        $coreTypeCell = preg_replace('/[^\w\d]/', '', $sheet->getCell('C15')->getValue());
                        $core_type = ($coreTypeCell === 'PDKSIDJ1') ? 'KONVEN' : 'SYARIAH';

                        $file_title = $file->getName();

                        $row = 21;
                        $imported = 0;
                        $rowErrors = [];
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
                            try {
                                $db->table('t_ct_bifast')->insert($data);
                                $imported++;
                            } catch (\Throwable $rowEx) {
                                $rowErrors[] = "File <b>{$file_title}</b> row <b>{$row}</b>: <span class='text-danger'>DB Error: {$rowEx->getMessage()}</span>";
                            }
                            $row++;
                        }
                        $msg = "File <b>{$file->getName()}</b>: <b>$imported</b> rows imported.";
                        if (!empty($rowErrors)) {
                            $msg .= "<br>" . implode('<br>', $rowErrors);
                        }
                        $results[] = $msg;
                    } catch (\Throwable $e) {
                        $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>Parse Error: {$e->getMessage()}</span>";
                    }
                } else {
                    $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>Invalid file.</span>";
                }
            }

            // Handle CSV files (new logic)
            $csvFiles = $files['xlsx_files_2'] ?? [];
            if (!is_array($csvFiles)) $csvFiles = [$csvFiles];
            foreach ($csvFiles as $fileIdx => $file) {
                if ($file->isValid() && strtolower($file->getExtension()) === 'csv') {
                    try {
                        $csvRows = [];
                        $handle = fopen($file->getTempName(), 'r');
                        if ($handle === false) {
                            $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>Cannot open file.</span>";
                            continue;
                        }
                        while (($row = fgetcsv($handle, 0, '|')) !== false) {
                            $csvRows[] = $row;
                        }
                        fclose($handle);

                        // Find the row index that contains "Ending Balance"
                        $limitIdx = null;
                        foreach ($csvRows as $idx => $row) {
                            if (in_array('Ending Balance', $row)) {
                                $limitIdx = $idx;
                                break;
                            }
                        }
                        if ($limitIdx === null) {
                            $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>No 'Ending Balance' row found.</span>";
                            continue;
                        }

                        // Generate unique code for this file
                        $unique_file = uniqid('memstat_', true);
                        $file_name = $file->getName();

                        // Insert data from B3:M{limitIdx} (index 2 to limitIdx-1)
                        $imported = 0;
                        $rowErrors = [];
                        for ($i = 2; $i < $limitIdx; $i++) {
                            $row = $csvRows[$i];
                            // Defensive: pad row to 13 columns
                            $row = array_pad($row, 13, null);
                            $data = [
                                'time' => $row[1] ?? null,
                                'transaction_id' => $row[2] ?? null,
                                'business_msg' => $row[3] ?? null,
                                'msg_id' => $row[4] ?? null,
                                'reff_number' => $row[5] ?? null,
                                'counterparty' => $row[6] ?? null,
                                'tx_service_type' => $row[7] ?? null,
                                'debit_amount' => $row[8] ?? null,
                                'credit_amount' => $row[9] ?? null,
                                'balance' => $row[10] ?? null,
                                'status' => $row[11] ?? null,
                                'status_code' => $row[12] ?? null,
                                'file_name' => $file_name,
                                'unique_file' => $unique_file,
                            ];
                            try {
                                $db->table('t_memstat')->insert($data);
                                $imported++;
                            } catch (\Throwable $rowEx) {
                                $rowErrors[] = "File <b>{$file->getName()}</b> row <b>" . ($i+1) . "</b>: <span class='text-danger'>DB Error: {$rowEx->getMessage()}</span>";
                            }
                        }
                        $msg = "File <b>{$file->getName()}</b>: <b>$imported</b> rows imported.";
                        if (!empty($rowErrors)) {
                            $msg .= "<br>" . implode('<br>', $rowErrors);
                        }
                        $results[] = $msg;
                    } catch (\Throwable $e) {
                        $results[] = "File <b>{$file->getName()}</b>: <span class='text-danger'>CSV Parse Error: {$e->getMessage()}</span>";
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'ok',
                'messages' => $results
            ]);
        } catch (\Throwable $ex) {
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