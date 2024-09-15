<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .invoice-title {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .table {
            margin-bottom: 0;
            border-width: 2px; 
        }

        .table thead th, 
        .table tbody td {
            border-width: 2px; 
            vertical-align: middle;
            text-align: left;
            padding: 2px; 
        }

        thead {
            display: table-header-group;
        }

        @media print {
            * {
                overflow: visible !important;
            }

            @page {
                size: letter portrait;
                margin: 0.5cm; 
            }


            body {
                width: 100%;
                overflow: visible;
            }

  
            body, .container {
                padding: 0;
                margin: 0;
                width: 100%;
            }

       
            .table {
                width: 100%;
                border-collapse: collapse;
            }

            .table thead th, 
            .table tbody td {
                padding: 5px;
                font-size: 12px;
            }

     
            .p-5 {
                padding: 0 !important;
            }

     
            .invoice-title {
                font-size: 1.3rem;
            }
 
            tr {
                page-break-inside: avoid;
            }

 
            tbody tr:nth-child(20n) {
                page-break-after: always;
              }
            
              tbody tr:nth-child(20n+1) {
                page-break-before: always;
              }
        }
    </style>
</head>

<body class="p-5">
    <div class="">
        <div class="row mb-4">
            <div class="col-2 d-flex align-items-center">
                <h1 class="invoice-title">Invoice</h1>
            </div>
            <div class="col-10">
                <table class="table table-bordered">
                    <thead>
                        <tr>
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

        <div class="row">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center px-2">No</th>
                            <th class="text-center px-2">Produk</th>
                            <th class="text-center px-2">Batch</th>
                            <th class="text-center px-2">ED</th>
                            <th class="text-center px-2">Kuantitas</th>
                            <th class="text-center px-2">Satuan</th>
                            <th class="text-end px-2">Harga</th>
                            <th class="text-end px-2">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                            @php
                            $no = 1;
                        @endphp
                        @foreach ($data['barangPenjualan'] as $item)
                            <tr>
                                <td class="text-center px-2">{{ $no++ }}</td>
                                <td class="text-start px-2">{{ $item['nama_barang'] }}</td>
                                <td class="text-center px-2">{{ $item['batch'] }}</td>
                                <td class="text-center px-2">{{ $item['exp_date'] }}</td>
                                <td class="text-end px-2">{{ $item['jumlah'] }}</td>
                                <td class="px-2">{{ $item['nama_satuan'] }}</td>
                                <td class="text-end px-2">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="text-end px-2">Rp {{ number_format($item['total_barang'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @foreach ($data['barangReturPenjualan'] as $item)
                            <tr>
                                <td class="text-center px-2">{{ $no++ }}</td>
                                <td class="text-start px-2">{{ $item['nama_barang'] }}</td>
                                <td class="text-center px-2">{{ $item['batch'] }}</td>
                                <td class="text-center px-2">{{ $item['exp_date'] }}</td>
                                <td class="text-end px-2">- {{ $item['jumlah_retur'] }}</td>
                                <td class="px-2">{{ $item['nama_satuan'] }}</td>
                                <td class="text-end px-2">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="text-end px-2">Rp {{ number_format($item['total_retur'], 0, ',', '.') }}</td>
                            </tr>   
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-end px-2">Subtotal</th>
                            <th class="text-end px-2">Pemotongan</th>
                            <th class="text-end px-2">Jumlah Retur</th>
                            <th class="text-end px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-end px-2 w-25">Rp {{ number_format($data['sub_total'], 0, ',', '.') }}</td>
                            <td class="text-end px-2 w-25">Rp {{ number_format($data['diskon_keseluruhan'], 0, ',', '.') }}</td>
                            <td class="text-end px-2 w-25">Rp {{ number_format($data['total_retur'], 0, ',', '.') }}</td>
                            <td class="text-end px-2 w-25"><strong>Rp {{ number_format($data['total'], 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <div>
                                    <p class="ps-2 pt-2"><strong>Catatan:</strong></p>
                                    <p class="ps-2 pt-2" >Barang yang sudah dibeli <strong>tidak dapat ditukar atau dikembalikan</strong>, kecuali barang expired sesuai dengan ketentuan BPOM dan Apotek Semoga Jaya.</p>   
                                </div>  
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
