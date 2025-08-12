@extends('layouts.app')
@section('content')
    <!-- Header dengan Real-time Clock -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="text-white">
                                <h1 class="display-5 fw-bold mb-2">
                                    <i class="fal fa-balance-scale mr-3"></i>
                                    Selamat Datang di SIRELA
                                </h1>
                                <p class="lead mb-0 opacity-90">Sistem Rekonsiliasi dan Pelimpahan Dana</p>
                                <p class="mb-0 opacity-75">Bank Kalsel - Kelola proses rekonsiliasi settlement dengan mudah dan akurat</p>
                            </div>
                        </div>
                        <div class="col-lg-4 text-right">
                            <div class="text-white">
                                <div class="display-4 fw-bold" id="live-clock">
                                    {{ date('H:i:s') }}
                                </div>
                                <p class="h5 mb-0" id="live-date">
                                    {{ date('l, d F Y') }}
                                </p>
                                <small class="opacity-75">Waktu Indonesia Tengah (WITA)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Rekonsiliasi Aktif -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="mr-4">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fal fa-calendar-check fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">Status Rekonsiliasi</h4>
                                    <p class="text-muted mb-2">
                                        @php
                                            // Helper untuk format tanggal Indonesia
                                            function tanggalIndo($tanggal) {
                                                $bulan = [
                                                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                                ];
                                                $tgl = date('d', strtotime($tanggal));
                                                $bln = $bulan[(int)date('m', strtotime($tanggal))];
                                                $thn = date('Y', strtotime($tanggal));
                                                return $tgl . ' ' . $bln . ' ' . $thn;
                                            }

                                            function hariIndo($tanggal) {
                                                $hari = [
                                                    'Sunday' => 'Minggu',
                                                    'Monday' => 'Senin',
                                                    'Tuesday' => 'Selasa',
                                                    'Wednesday' => 'Rabu',
                                                    'Thursday' => 'Kamis',
                                                    'Friday' => 'Jumat',
                                                    'Saturday' => 'Sabtu'
                                                ];
                                                $en = date('l', strtotime($tanggal));
                                                return $hari[$en] ?? $en;
                                            }

                                            if ($tgl_rekon) {
                                                $tanggalAktif = $tgl_rekon->TGL_REKON;
                                                $tanggalFormatted = hariIndo($tanggalAktif) . ', ' . tanggalIndo($tanggalAktif);
                                                $hariIni = date('Y-m-d');
                                                $selisihHari = floor((strtotime($hariIni) - strtotime($tanggalAktif)) / (60*60*24));

                                                echo "Tanggal Aktif: <strong class='text-primary'>{$tanggalFormatted}</strong>";
                                                if ($selisihHari == 0) {
                                                    echo " <span class='badge badge-success'>Hari Ini</span>";
                                                } elseif ($selisihHari > 0) {
                                                    if ($selisihHari >= 15) {
                                                        echo " <span class='badge badge-warning text-white'>{$selisihHari} hari yang lalu</span>";
                                                    } else {
                                                        echo " <span class='badge badge-warning'>{$selisihHari} hari yang lalu</span>";
                                                    }
                                                } else {
                                                    echo " <span class='badge badge-info'>" . abs($selisihHari) . " hari ke depan</span>";
                                                }
                                            } else {
                                                echo "<span class='text-warning'>Belum ada tanggal rekonsiliasi yang aktif</span>";
                                            }
                                        @endphp
                                    </p>
                                    <small class="text-muted">Untuk memulai rekonsiliasi periode baru, silakan setup tanggal melalui menu persiapan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-right">
                            <a href="{{ site_url('rekon') }}" class="btn btn-primary btn-lg">
                                <i class="fal fa-plus-circle mr-2"></i>
                                Setup Rekonsiliasi Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fal fa-rocket text-primary mr-2"></i>
                        Mulai Proses Rekonsiliasi
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Ikuti langkah-langkah berikut untuk memulai proses rekonsiliasi settlement:</p>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <a href="{{ site_url('rekon') }}" class="btn btn-outline-primary btn-lg w-100 text-left py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle text-white fw-bold mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">1</div>
                                    <div>
                                        <div class="fw-bold">Upload File Settlement</div>
                                        <small class="text-muted text-sm">Unggah file data dari berbagai sumber</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 mb-3">
                            <a href="{{ site_url('rekon/step2') }}" class="btn btn-outline-success btn-lg w-100 text-left py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success rounded-circle text-white fw-bold mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">2</div>
                                    <div>
                                        <div class="fw-bold">Validasi & Mapping Data</div>
                                        <small class="text-muted text-sm">Periksa dan sesuaikan format data</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="{{ site_url('rekon/step2') }}" class="btn btn-outline-warning btn-lg w-100 text-left py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning rounded-circle text-white fw-bold mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">3</div>
                                    <div>
                                        <div class="fw-bold">Konfirmasi & Proses</div>
                                        <small class="text-muted text-sm">Finalisasi dan jalankan rekonsiliasi</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fal fa-chart-line text-success mr-2"></i>
                        Menu Analisis & Laporan
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Akses menu analisis dan laporan untuk data yang sudah diproses:</p>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ site_url('rekon/process/detail-vs-rekap') }}" class="btn btn-outline-info btn-block py-3">
                                <i class="fal fa-balance-scale fa-2x d-block mb-2"></i>
                                <span class="fw-bold small">Laporan Detail vs Rekap</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ site_url('rekon/process/direct-jurnal-rekap') }}" class="btn btn-outline-success btn-block py-3">
                                <i class="fal fa-file-invoice fa-2x d-block mb-2"></i>
                                <span class="fw-bold small">Rekap Tx Direct Jurnal</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ site_url('rekon/process/penyelesaian-dispute') }}" class="btn btn-outline-danger btn-block py-3">
                                <i class="fal fa-exclamation-triangle fa-2x d-block mb-2"></i>
                                <span class="fw-bold small">Penyelesaian Dispute</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ site_url('rekon/process/indirect-dispute') }}" class="btn btn-outline-warning btn-block py-3">
                                <i class="fal fa-question-circle fa-2x d-block mb-2"></i>
                                <span class="fw-bold small">Penyelesaian Dispute</span>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="{{ site_url('rekon/process/indirect-jurnal-rekap') }}" class="btn btn-outline-secondary btn-block py-3">
                                <i class="fal fa-file-alt mr-2"></i>
                                <span class="fw-bold">Rekap Tx Indirect Jurnal</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panduan & Tips -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fal fa-lightbulb text-warning mr-2"></i>
                        Cara Penggunaan Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="media">
                                <div class="bg-primary rounded-circle text-white mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fal fa-upload"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="fw-bold">1. Persiapan Data</h6>
                                    <p class="text-muted small mb-0">Upload file data agregator detail, settlement education, settlement pajak (format text), dan data M-Gate (format CSV)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="media">
                                <div class="bg-success rounded-circle text-white mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fal fa-cogs"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="fw-bold">2. Validasi Data</h6>
                                    <p class="text-muted small mb-0">Sistem akan memvalidasi format dan kelengkapan data, serta melakukan mapping field</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="media">
                                <div class="bg-warning rounded-circle text-white mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fal fa-sync"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="fw-bold">3. Proses Matching</h6>
                                    <p class="text-muted small mb-0">Sistem akan mencocokkan data dan mengidentifikasi transaksi yang sesuai atau bermasalah</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="media">
                                <div class="bg-danger rounded-circle text-white mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </div>
                                <div class="media-body">
                                    <h6 class="fw-bold">4. Resolusi Dispute</h6>
                                    <p class="text-muted small mb-0">Tangani transaksi yang tidak match dan dokumentasikan penyelesaian dispute</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fal fa-info-circle text-info mr-2"></i>
                        Informasi Penting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <h6 class="alert-heading">
                            <i class="fal fa-exclamation-circle mr-1"></i>
                            Perhatian
                        </h6>
                        <ul class="mb-0 small">
                            <li>Pastikan file yang diupload dalam format yang benar</li>
                            <li>Proses rekonsiliasi dilakukan step by step</li>
                            <li>Hubungi administrator jika mengalami kendala</li>
                            <li>Selalu lakukan pengecekan ulang hasil rekonsiliasi sebelum finalisasi</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Fungsi untuk update jam real-time
function updateClock() {
    const now = new Date();
    
    // Update jam dengan detik
    const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    
    // Update tanggal dalam bahasa Indonesia
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const dateString = now.toLocaleDateString('id-ID', options);
    
    // Update elemen DOM
    document.getElementById('live-clock').textContent = timeString;
    document.getElementById('live-date').textContent = dateString;
}

// Update jam saat halaman dimuat dan setiap detik
updateClock();
setInterval(updateClock, 1000);

// Efek animasi untuk tombol
$(document).ready(function() {
    $('.btn').hover(
        function() {
            $(this).addClass('shadow');
        },
        function() {
            $(this).removeClass('shadow');
        }
    );
});
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

#live-clock {
    font-family: 'Courier New', monospace;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.media {
    transition: transform 0.2s ease-in-out;
}

.media:hover {
    transform: translateX(5px);
}
</style>
@endpush
