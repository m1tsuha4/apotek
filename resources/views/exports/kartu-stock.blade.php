<table>
    <thead>
        <tr>
            <th>Nama Barang</th>
            <th>Nama Satuan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $commonData['nama_barang'] }}</td>
            <td>{{ $commonData['nama_satuan'] }}</td>
        </tr>
    </tbody>
</table>
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Batch</th>
            <th>Exp Date</th>
            <th>Masuk</th>
            <th>Keluar</th>
            <th>Sisa</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stockDetails as $detail)
            <tr>
                <td>{{ $detail['tanggal'] }}</td>
                <td>{{ $detail['batch'] }}</td>
                <td>{{ $detail['exp_date'] }}</td>
                <td>{{ $detail['masuk'] }}</td>
                <td>{{ $detail['keluar'] }}</td>
                <td>{{ $detail['sisa'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
