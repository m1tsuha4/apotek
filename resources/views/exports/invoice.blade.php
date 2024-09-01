<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .table th {
            text-transform: uppercase;
            font-size: 12px;
        }
        .table td {
            font-size: 12px;
        }
    </style>
</head>
<body class="container mt-5" style="max-width: 1200px;">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold">Invoice</h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Nama Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Kode Dokumen</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $data['nama_pelanggan'] }}</td>
                        <td>{{ $data['tanggal'] }}</td>
                        <td>{{ $data['id_penjualan'] }}</td>
                        <td>{{ $data['status'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Produk</th>
                        <th>Batch</th>
                        <th>Ed</th>
                        <th>Kuantitas</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($data['barangPenjualan'] as $item)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td>{{ $item['batch'] }}</td>
                        <td>{{ $item['exp_date'] }}</td>
                        <td>{{ $item['jumlah'] }}</td>
                        <td>{{ $item['nama_satuan'] }}</td>
                        <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <table class="table table-bordered">
                <thead class="text-center">
                    <tr>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Pemotongan</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-end">Rp {{ number_format($data['sub_total'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($data['diskon'] + $data['total_diskon_satuan'], 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($data['sisa_tagihan'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="border-top pt-2">
                <h5 class="fw-bold">Catatan</h5>
                <p>{{ $data['catatan'] }}</p>
            </div>
        </div>
    </div>
</body>
</html>
