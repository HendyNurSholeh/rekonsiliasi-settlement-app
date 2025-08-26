<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">
        <i class="fal fa-exclamation-triangle"></i> Proses Sudah Ada
    </h5>
    <p>{{ session('warning') }}</p>
    <hr>
    <p class="mb-0">
        <strong>Pilihan Anda:</strong><br>
        • <strong>Batalkan:</strong> Kembali dan pilih tanggal lain<br>
        • <strong>Reset & Lanjutkan:</strong> Hapus semua data rekonsiliasi untuk tanggal tersebut dan buat proses baru
    </p>
    <div class="mt-3">
        <button type="button" class="btn btn-danger" onclick="confirmReset(`{{ session('existing_date') }}`)">
            <i class="fal fa-redo"></i> Reset & Lanjutkan
        </button>
        <button type="button" class="btn btn-secondary ml-2" onclick="dismissAlert()">
            <i class="fal fa-times"></i> Batalkan
        </button>
    </div>
</div>