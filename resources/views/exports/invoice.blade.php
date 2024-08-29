<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link ke font Inter dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Terapkan font Inter ke seluruh halaman */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body style="width: 1200px;" class="p-5">
    <div class="flex justify-start items-center p-4 pe-0">
        <div class="w-fit px-5">
            <p class="text-xl font-bold">Invoice</p>
        </div>
        <div class="w-full">
            <table class="w-full table-auto border border-dark-3 border-collapse">
                <thead>
                    <tr class="uppercase text-sm leading-normal">
                        <th class="px-6 text-left border border-dark-3">Nama Pelanggan</th>
                        <th class="px-6 text-left border border-dark-3">Tanggal</th>
                        <th class="px-6 text-left border border-dark-3">Kode Dokumen</th>
                        <th class="px-6 text-left border border-dark-3">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-light">
                    <tr>
                        <td class="px-6 text-left border border-dark-3">{{ $data['nama_pelanggan'] }}</td>
                        <td class="px-6 text-left border border-dark-3">{{ $data['tanggal'] }}</td>
                        <td class="px-6 text-left border border-dark-3">{{ $data['id_penjualan'] }}</td>
                        <td class="px-6 text-left border border-dark-3">{{ $data['status'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        <table class="w-full table-auto border border-dark-3 border-collapse">
            <thead>
                <tr class="uppercase text-sm leading-normal">
                    <th class="px-6 text-left border border-dark-3">No</th>
                    <th class="px-6 text-left border border-dark-3">Produk</th>
                    <th class="px-6 text-left border border-dark-3">Batch</th>
                    <th class="px-6 text-left border border-dark-3">Ed</th>
                    <th class="px-6 text-left border border-dark-3">Kuantitas</th>
                    <th class="px-6 text-left border border-dark-3">Satuan</th>
                    <th class="px-6 text-left border border-dark-3">Harga</th>
                    <th class="px-6 text-left border border-dark-3">Jumlah</th>
                </tr>
            </thead>
            <tbody class="text-sm font-light">
                @php
                    $no = 1;
                @endphp
                @foreach ($data['barangPenjualan'] as $item)
                <tr>
                    <td class="px-6 text-left border border-dark-3">{{ $no++ }}</td>
                    <td class="px-6 text-left border border-dark-3">{{ $item['nama_barang'] }}</td>
                    <td class="px-6 text-left border border-dark-3">{{ $item['batch'] }}</td>
                    <td class="px-6 text-left border border-dark-3">{{ $item['exp_date'] }}</td>
                    <td class="px-6 text-left border border-dark-3">{{ $item['jumlah'] }}</td>
                    <td class="px-6 text-left border border-dark-3">{{ $item['nama_satuan'] }}</td>
                    <td class="px-6 text-left border border-dark-3">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td class="px-6 text-left border border-dark-3">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <table class="w-full table-auto border border-dark-3 border-collapse">
            <thead class="uppercase text-sm leading-normal">
                <tr>
                    <th class="px-6 text-left border border-dark-3 text-right w-1/3">Subtotal</th>
                    <th class="px-6 text-left border border-dark-3 text-right w-1/3">Pemotongan</th>
                    <th class="px-6 text-left border border-dark-3 text-right w-1/3">Total</th>
                </tr>
            </thead>
            <tbody class="text-sm font-light">
                <tr>
                    <td class="px-6 text-left border border-dark-3 text-right w-1/3">Rp {{ number_format($data['sub_total'], 0, ',', '.') }}</td>
                    <td class="px-6 text-left border border-dark-3 text-right w-1/3">Rp {{ number_format($data['diskon'] + $data['total_diskon_satuan'], 0, ',', '.') }}</td>
                    <td class="px-6 text-left border border-dark-3 text-right font-bold w-1/3">Rp {{ number_format($data['sisa_tagihan'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="border border-t-0">
        <div class="ps-5 pb-5 pt-2">
            <p class="font-bold">Catatan</p>
            <p>{{ $data['catatan'] }}</p>
        </div>
    </div>
</body>
</html>
