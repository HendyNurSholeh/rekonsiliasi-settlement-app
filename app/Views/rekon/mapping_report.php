<!DOCTYPE html>
<html>
<head>
    <title>Product Mapping Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .stats { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .unmapped { background-color: #fff3cd; }
        .mapped { background-color: #d4edda; }
        .center { text-align: center; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #212529; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Product Mapping Report</h2>
        <p>Tanggal: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <div class="stats">
        <h4>Statistik Mapping</h4>
        <p><strong>Total Produk:</strong> <?= $statistics['total_produk'] ?></p>
        <p><strong>Produk Termapping:</strong> <?= $statistics['mapped_produk'] ?></p>
        <p><strong>Produk Belum Termapping:</strong> <?= $statistics['unmapped_produk'] ?></p>
        <p><strong>Persentase Mapping:</strong> <?= $statistics['mapping_percentage'] ?>%</p>
    </div>

    <?php if (!empty($unmapped_products)): ?>
    <h4>Produk yang Belum Termapping</h4>
    <table>
        <thead>
            <tr>
                <th class="center">Source</th>
                <th>Kode/Nama Produk</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($unmapped_products as $product): ?>
            <tr class="unmapped">
                <td class="center"><?= $product['SOURCE'] ?></td>
                <td><?= $product['PRODUK'] ?></td>
                <td class="center">
                    <span class="badge badge-warning">Not Mapped</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h4>Semua Data Mapping</h4>
    <table>
        <thead>
            <tr>
                <th class="center">Source</th>
                <th>Kode/Nama Produk</th>
                <th>Nama Group</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_mapping as $mapping): ?>
            <tr class="<?= empty($mapping['NAMA_GROUP']) ? 'unmapped' : 'mapped' ?>">
                <td class="center"><?= $mapping['SOURCE'] ?></td>
                <td><?= $mapping['PRODUK'] ?></td>
                <td><?= $mapping['NAMA_GROUP'] ?? '-' ?></td>
                <td class="center">
                    <?php if (empty($mapping['NAMA_GROUP'])): ?>
                        <span class="badge badge-warning">Not Mapped</span>
                    <?php else: ?>
                        <span class="badge badge-success">Mapped</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 12px; color: #666;">
        <p><strong>Catatan:</strong></p>
        <ul>
            <li>Produk dengan status "Not Mapped" perlu ditambahkan ke tabel <code>t_group_settlement</code></li>
            <li>SOURCE "DETAIL" menggunakan field PRODUK dari tamp_agn_detail</li>
            <li>SOURCE "REKAP" menggunakan KODE_PRODUK dari tamp_agn_settle_pajak</li>
        </ul>
    </div>
</body>
</html>
