<?php

namespace App\Controllers\Upload;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;

class UploadData extends BaseController
{
    use HasLogActivity;

    public function agregator()
    {
        $data = [
            'title' => 'Upload Data Agregator',
            'route' => 'upload/agregator'
        ];

        return $this->render('upload/agregator.blade.php', $data);
    }

    public function settlementPajak()
    {
        $data = [
            'title' => 'Upload File Settlement Pajak',
            'route' => 'upload/settlement-pajak'
        ];

        return $this->render('upload/settlement_pajak.blade.php', $data);
    }

    public function settlementEdu()
    {
        $data = [
            'title' => 'Upload File Settlement Edu',
            'route' => 'upload/settlement-edu'
        ];

        return $this->render('upload/settlement_edu.blade.php', $data);
    }

    public function mgate()
    {
        $data = [
            'title' => 'Upload File MGate',
            'route' => 'upload/mgate'
        ];

        return $this->render('upload/mgate.blade.php', $data);
    }

    public function processUpload()
    {
        $uploadedFile = $this->request->getFile('file_upload');
        $fileType = $this->request->getPost('file_type');
        
        if (!$uploadedFile->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau terjadi kesalahan upload');
        }

        // Validasi tipe file (Excel/CSV)
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Tipe file tidak diizinkan. Hanya file Excel (.xls, .xlsx) dan CSV yang diperbolehkan');
        }

        // Pindahkan file ke direktori upload
        $fileName = $uploadedFile->getRandomName();
        $uploadPath = WRITEPATH . 'uploads/rekon/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $uploadedFile->move($uploadPath, $fileName);

        // Log activity
        $this->logActivity([
            'action' => 'upload_file',
            'description' => 'Upload file ' . $fileType . ': ' . $uploadedFile->getName(),
            'file_name' => $fileName,
            'file_type' => $fileType
        ]);

        return redirect()->back()->with('success', 'File berhasil diupload: ' . $uploadedFile->getName());
    }
}
