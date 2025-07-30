@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-gavel"></i> {{ $title }}
        <small>Penyelesaian dispute direct jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Penyelesaian Dispute</strong> 
            <br>Menampilkan dan mengelola data dispute yang memerlukan penyelesaian manual.
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-filter"></i> Filter Data
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ current_url() }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-table"></i> Data Dispute Resolution
                </h5>
            </div>
            <div class="card-body">
                @if(!empty($disputeData))
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="disputeTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>ID Partner</th>
                                    <th>Terminal ID</th>
                                    <th>Produk</th>
                                    <th>ID Pelanggan</th>
                                    <th>RP Biller Tag</th>
                                    <th>Status Biller</th>
                                    <th>Status Core</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputeData as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['IDPARTNER'] ?? '' }}</td>
                                    <td>{{ $item['TERMINALID'] ?? '' }}</td>
                                    <td><code>{{ $item['PRODUK'] ?? '' }}</code></td>
                                    <td>{{ $item['IDPEL'] ?? '' }}</td>
                                    <td>{{ number_format($item['RP_BILLER_TAG'] ?? 0) }}</td>
                                    <td>
                                        @php
                                            $statusBiller = $item['STATUS_BILLER'] ?? 0;
                                        @endphp
                                        @if($statusBiller == 0)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($statusBiller == 1)
                                            <span class="badge badge-success">Sukses</span>
                                        @else
                                            <span class="badge badge-danger">Gagal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusCore = $item['STATUS_CORE'] ?? 0;
                                        @endphp
                                        @if($statusCore == 0)
                                            <span class="badge badge-danger">Tidak Terdebet</span>
                                        @else
                                            <span class="badge badge-primary">Terdebet</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="openDisputeModal('{{ $item['v_ID'] ?? '' }}')">
                                            <i class="fal fa-edit"></i> Proses
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fal fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data ditemukan</h5>
                        <p class="text-muted">Silakan pilih tanggal rekonsiliasi untuk menampilkan data.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Proses Data Dispute -->
<div class="modal fade" id="disputeModal" tabindex="-1" role="dialog" aria-labelledby="disputeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disputeModalLabel">
                    <i class="fal fa-edit"></i> Proses Data Dispute
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="disputeForm">
                    <input type="hidden" id="dispute_id" name="id">
                    
                    <!-- Data Transaksi -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Data Transaksi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ID Partner</label>
                                        <input type="text" class="form-control" id="modal_idpartner" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Terminal ID</label>
                                        <input type="text" class="form-control" id="modal_terminalid" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Produk</label>
                                        <input type="text" class="form-control" id="modal_produk" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ID Pelanggan</label>
                                        <input type="text" class="form-control" id="modal_idpel" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tagihan -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Data Tagihan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Pokok</label>
                                        <input type="text" class="form-control" id="modal_rp_pokok" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Admin</label>
                                        <input type="text" class="form-control" id="modal_rp_admin" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Tag</label>
                                        <input type="text" class="form-control" id="modal_rp_tag" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Rekonsiliasi -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Status Rekonsiliasi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ID Partner (Channel) <span class="text-danger">*</span></label>
                                        <select class="form-control" id="modal_channel" name="idpartner" required>
                                            <option value="">Pilih Channel</option>
                                            <option value="CHANNEL KON">CHANNEL KON</option>
                                            <option value="CHANNEL SYA">CHANNEL SYA</option>
                                            <option value="VA DIGITAL KON">VA DIGITAL KON</option>
                                            <option value="VA DIGITAL SYA">VA DIGITAL SYA</option>
                                            <option value="PPOB KON">PPOB KON</option>
                                            <option value="PPOB SYA">PPOB SYA</option>
                                            <option value="MITRACOMM">MITRACOMM</option>
                                            <option value="POS INDONESIA">POS INDONESIA</option>
                                            <option value="GO-PAY">GO-PAY</option>
                                            <option value="ARTAJASA">ARTAJASA</option>
                                            <option value="PDAM BARITO KUALA">PDAM BARITO KUALA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Biller <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="biller_sukses" value="1">
                                                <label class="form-check-label" for="biller_sukses">Sukses</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="biller_pending" value="0">
                                                <label class="form-check-label" for="biller_pending">Pending</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="biller_gagal" value="2">
                                                <label class="form-check-label" for="biller_gagal">Gagal</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Core <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_core" id="core_terdebet" value="1">
                                                <label class="form-check-label" for="core_terdebet">Terdebet</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_core" id="core_tidak_terdebet" value="0">
                                                <label class="form-check-label" for="core_tidak_terdebet">Tidak Terdebet</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Settlement <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="settlement_dilimpahkan" value="1">
                                                <label class="form-check-label" for="settlement_dilimpahkan">Dilimpahkan</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="settlement_revershal" value="8">
                                                <label class="form-check-label" for="settlement_revershal">Transaksi di Revershal</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="settlement_gagal" value="9">
                                                <label class="form-check-label" for="settlement_gagal">Transaksi Gagal</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveDispute()">
                    <i class="fal fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function() {
    @if(!empty($disputeData))
        $('#disputeTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: [0, 8], orderable: false }
            ]
        });
    @endif
});

function openDisputeModal(id) {
    if (!id) {
        showAlert('error', 'ID tidak ditemukan');
        return;
    }

    // Clear form
    $('#disputeForm')[0].reset();
    $('#dispute_id').val(id);
    
    // Get dispute detail
    $.ajax({
        url: '{{ base_url('rekon/process/direct-jurnal/dispute/detail') }}',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Fill readonly fields
                $('#modal_idpartner').val(data.IDPARTNER || '');
                $('#modal_terminalid').val(data.TERMINALID || '');
                $('#modal_produk').val(data.PRODUK || '');
                $('#modal_idpel').val(data.IDPEL || '');
                $('#modal_rp_pokok').val(formatNumber(data.RP_BILLER_POKOK || 0));
                $('#modal_rp_admin').val(formatNumber(data.RP_BILLER_ADMIN || 0));
                $('#modal_rp_tag').val(formatNumber(data.RP_BILLER_TAG || 0));
                
                // Set current values for radio buttons
                $('input[name="status_biller"][value="' + (data.STATUS_BILLER || '0') + '"]').prop('checked', true);
                $('input[name="status_core"][value="' + (data.STATUS_CORE || '0') + '"]').prop('checked', true);
                
                $('#disputeModal').modal('show');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Terjadi kesalahan saat mengambil data');
        }
    });
}

function saveDispute() {
    const formData = new FormData($('#disputeForm')[0]);
    
    // Validate required fields
    if (!formData.get('idpartner') || !formData.get('status_biller') || 
        !formData.get('status_core') || !formData.get('status_settlement')) {
        showAlert('warning', 'Mohon lengkapi semua field yang wajib diisi');
        return;
    }
    
    $.ajax({
        url: '{{ base_url('rekon/process/direct-jurnal/dispute/update') }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#disputeModal').modal('hide');
                // Reload page to refresh data
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Terjadi kesalahan saat menyimpan data');
        }
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function showAlert(type, message) {
    let alertClass = 'alert-info';
    let icon = 'fa-info-circle';
    
    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            break;
        case 'error':
            alertClass = 'alert-danger';
            icon = 'fa-exclamation-circle';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            icon = 'fa-exclamation-triangle';
            break;
    }
    
    let alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fal ${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('.subheader').after(alertHtml);
    
    // Auto hide success alerts
    if (type === 'success') {
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 3000);
    }
}
</script>
@endpush

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.table thead th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.modal-lg {
    max-width: 900px;
}

.form-check-inline {
    margin-right: 1rem;
}

.text-danger {
    color: #dc3545 !important;
}
</style>
@endpush
