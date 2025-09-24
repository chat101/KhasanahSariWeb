<table>
    <thead>
        <tr>
            <th colspan="{{ 2 + count($days) }}" style="text-align:center; font-weight:bold; font-size:14pt;">
                LAPORAN PRODUKSI BULANAN
            </th>
        </tr>
        <tr>
            <th colspan="{{ 2 + count($days) }}" style="text-align:center; font-weight:bold;">
                BULAN {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Produk</th>
            @foreach($days as $d)
                <th>{{ $d }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['no'] }}</td>
            <td>{{ $row['produk'] }}</td>
            @foreach($days as $d)
                <td>{{ $row['days'][$d] ?? '' }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
