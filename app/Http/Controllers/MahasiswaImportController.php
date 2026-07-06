<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MahasiswaImportController extends Controller
{
    public function form()
    {
        return view('admin.mahasiswa.import-excel');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib diunggah.',
            'file_excel.mimes'    => 'File harus berformat .xlsx atau .xls.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file_excel')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $dataDariExcel = [];
        $sudahAda      = [];
        $belumAda      = [];

        // Asumsi Baris 1 adalah Header (NIM, Nama, Email, Program Studi)
        // Mulai looping dari baris 2
        foreach ($rows as $noRow => $row) {
            if ($noRow == 1) continue; 

            $nim   = trim((string) ($row['A'] ?? ''));
            $nama  = trim((string) ($row['B'] ?? ''));
            $email = strtolower(trim((string) ($row['C'] ?? '')));
            $prodi = trim((string) ($row['D'] ?? ''));

            // Lewati baris kosong
            if (empty($nim) || empty($nama)) continue;

            $mhs = [
                'nim'   => $nim,
                'nama'  => $nama,
                'email' => $email ?: ($nim . '@mahasiswa.kampus.ac.id'), // Generate email jika kosong
                'prodi' => $prodi
            ];

            $dataDariExcel[] = $mhs;

            // Cek apakah NIM sudah terdaftar di database
            $cekDb = User::where('nip_nim', $nim)->orWhere('email', $mhs['email'])->first();

            if ($cekDb) {
                $sudahAda[] = ['excel' => $mhs, 'db' => $cekDb];
            } else {
                $belumAda[] = $mhs;
            }
        }

        if (empty($dataDariExcel)) {
            return back()->with('error', 'Tidak ada data mahasiswa ditemukan. Pastikan format Excel sesuai template.');
        }

        return view('admin.mahasiswa.import-excel', compact(
            'dataDariExcel', 'sudahAda', 'belumAda'
        ));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'mahasiswa'                 => 'required|array|min:1',
            'mahasiswa.*.nim'           => 'required|string|max:30',
            'mahasiswa.*.nama'          => 'required|string|max:200',
            'mahasiswa.*.email'         => 'required|email|max:200',
            'mahasiswa.*.program_studi' => 'nullable|string|max:100',
        ]);

        $berhasil = 0;
        $gagal    = [];

        foreach ($request->mahasiswa as $idx => $data) {
            // Guard: Pastikan NIM dan Email benar-benar belum terdaftar
            if (User::where('nip_nim', $data['nim'])->orWhere('email', $data['email'])->exists()) {
                $gagal[] = "NIM {$data['nim']} atau Email {$data['email']} sudah digunakan.";
                continue;
            }

            User::create([
                'name'          => trim($data['nama']),
                'email'         => strtolower(trim($data['email'])),
                'password'      => Hash::make(trim($data['nim'])), // Password default disamakan dengan NIM
                'role'          => 'mahasiswa',
                'nip_nim'       => trim($data['nim']),
                'program_studi' => trim($data['program_studi'] ?? ''),
                'is_active'     => true,
            ]);

            $berhasil++;
        }

        $pesan = "<strong>{$berhasil}</strong> mahasiswa berhasil didaftarkan. Password default adalah NIM masing-masing.";
        if (!empty($gagal)) {
            $pesan .= " Namun ada " . count($gagal) . " data yang gagal diimpor karena duplikasi.";
            return redirect()->route('admin.users.index')->with('warning', $pesan);
        }

        return redirect()->route('admin.users.index')->with('success', $pesan);
    }
}