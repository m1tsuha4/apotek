<!DOCTYPE html>
<html>
<head>
    <title>Penjualan Data</title>
</head>
<body>
    <h1>Penjualan Data</h1>
    <p>ID: {{ $data['id'] }}</p>
    <p>Status: {{ $data['status'] }}</p>
    <p>Nama Pelanggan: {{ $data['nama_pelanggan'] }}</p>
    <p>No Telepon: {{ $data['no_telepon'] }}</p>
    <p>Nama Jenis: {{ $data['nama_jenis'] }}</p>
    <p>Tanggal: {{ $data['tanggal'] }}</p>
    <p>Tanggal Jatuh Tempo: {{ $data['tanggal_jatuh_tempo'] }}</p>
    <p>Referensi: {{ $data['referensi'] }}</p>
    <p>Sub Total: {{ $data['sub_total'] }}</p>
    <p>Total Diskon Satuan: {{ $data['total_diskon_satuan'] }}</p>
    <p>Diskon: {{ $data['diskon'] }}</p>
    <p>Total: {{ $data['total'] }}</p>
    <p>Catatan: {{ $data['catatan'] }}</p>
    <p>Sisa Tagihan: {{ $data['sisa_tagihan'] }}</p>
    <h2>Barang Penjualan</h2>
    <ul>
        @foreach ($data['barangPenjualan'] as $barang)
            <li>
                Nama Barang: {{ $barang['nama_barang'] }} | 
                Jumlah: {{ $barang['jumlah'] }} | 
                Harga: {{ $barang['harga'] }} | 
                Total: {{ $barang['total'] }}
            </li>
        @endforeach
    </ul>
    <h2>Pembayaran Penjualan</h2>
    <ul>
        @foreach ($data['pembayaranPenjualan'] as $pembayaran)
            <li>
                Tanggal Pembayaran: {{ $pembayaran['tanggal_pembayaran'] }} | 
                Metode Pembayaran: {{ $pembayaran['metode_pembayaran'] }} | 
                Total Dibayar: {{ $pembayaran['total_dibayar'] }}
            </li>
        @endforeach
    </ul>
</body>
</html>
