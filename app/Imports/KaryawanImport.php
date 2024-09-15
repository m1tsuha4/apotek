<?php

namespace App\Imports;

use App\Models\Karyawan;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class KaryawanImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $karyawan = Karyawan::where('nama_karyawan', $row['nama_karyawan'])->first();

        // Cek apakah tanggal dalam format excel (numeric)
        if (is_numeric($row['tanggal_bergabung'])) {
            // Konversi tanggal Excel menjadi format Y-m-d
            $tanggal = Date::excelToDateTimeObject($row['tanggal_bergabung'])->format('Y-m-d');
        } else {
            // Jika tanggal dalam format d/m/y, gunakan Carbon untuk parsing
            $tanggal = Carbon::createFromFormat('d/m/Y', $row['tanggal_bergabung'])->format('Y-m-d');
        }

        if ($karyawan) {
            // Update the existing record
            $karyawan->update([
                'jenis_kelamin' => $row['jenis_kelamin'],
                'posisi' => $row['posisi'],
                'tanggal_bergabung' => $tanggal,
                'jumlah_gaji' => $row['jumlah_gaji'],
            ]);
            return $karyawan;
        } else {
            // Create a new record
            return new Karyawan([
                'nama_karyawan' => $row['nama_karyawan'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'posisi' => $row['posisi'],
                'tanggal_bergabung' => $tanggal,
                'jumlah_gaji' => $row['jumlah_gaji'],
            ]);
        }
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_karyawan' => 'required|string',
            'jenis_kelamin' => 'required|string',
            'posisi' => 'required|string',
            'tanggal_bergabung' => 'required',
        ];
    }
}
