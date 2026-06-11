<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * DosenImportController
 *
 * Fitur: scan file Excel jadwal → deteksi nama dosen yang belum terdaftar
 * → tampilkan daftar → admin konfirmasi & simpan ke database.
 *
 * Alur:
 *   GET  /admin/dosen-import          → form upload
 *   POST /admin/dosen-import/scan     → proses scan, tampilkan hasil
 *   POST /admin/dosen-import/simpan   → simpan dosen yang dipilih
 */
class DosenImportController extends Controller
{
    // ──────────────────────────────────────────────────────
    // 1. Form Upload
    // ──────────────────────────────────────────────────────

    public function form()
    {
        return view('admin.dosen.import-excel');
    }

    public function formManual()
    {
        return view('admin.dosen.import-manual');
    }

    // ──────────────────────────────────────────────────────
    // 2. Scan Excel → deteksi dosen baru vs sudah ada
    // ──────────────────────────────────────────────────────

    public function scan(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib diunggah.',
            'file_excel.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file_excel.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file_excel')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        // ── Deteksi baris header dan kolom Pengajar ──────
        $barisHeader = null;
        $kolomPengajar = null;

        foreach ($rows as $noRow => $row) {
            foreach ($row as $col => $val) {
                $valLower = strtolower(trim((string) $val));
                if (in_array($valLower, ['pengajar', 'dosen', 'nama dosen'])) {
                    $barisHeader   = $noRow;
                    $kolomPengajar = $col;
                    break 2;
                }
            }
        }

        if (!$kolomPengajar) {
            return back()->with('error',
                'Kolom "Pengajar" tidak ditemukan. Pastikan file Excel memiliki kolom dengan header "Pengajar" atau "Dosen".'
            );
        }

        // ── Ekstrak semua nama dosen dari file ───────────
        $namaDariExcel = [];
        foreach ($rows as $noRow => $row) {
            if ($noRow <= $barisHeader) continue;

            $nilaiSel = trim((string) ($row[$kolomPengajar] ?? ''));
            if (empty($nilaiSel) || $nilaiSel === 'nan') continue;

            // Beberapa dosen dipisah dengan " / "
            $parts = preg_split('/\s*\/\s*/', $nilaiSel);
            foreach ($parts as $part) {
                $nama = $this->bersihkanNama(trim($part));
                if ($nama && !in_array($nama, $namaDariExcel)) {
                    $namaDariExcel[] = $nama;
                }
            }
        }

        if (empty($namaDariExcel)) {
            return back()->with('error', 'Tidak ada nama dosen yang ditemukan di kolom Pengajar.');
        }

        // ── Bandingkan dengan database ───────────────────
        $semuaDosen   = User::dosen()->get(['id', 'name']);
        $sudahAda     = [];  // [nama_excel => User (match)]
        $belumAda     = [];  // [nama_excel => nama_excel]
        $mirip        = [];  // [nama_excel => [kandidat User, ...]]

        foreach ($namaDariExcel as $namaExcel) {
            $match = $this->cariMatch($namaExcel, $semuaDosen);

            if ($match['tepat']) {
                $sudahAda[$namaExcel] = $match['tepat'];
            } elseif (!empty($match['mirip'])) {
                $mirip[$namaExcel]    = $match['mirip'];
            } else {
                $belumAda[$namaExcel] = $namaExcel;
            }
        }

        return view('admin.dosen.import-excel', compact(
            'sudahAda', 'belumAda', 'mirip', 'namaDariExcel'
        ));
    }

    // ──────────────────────────────────────────────────────
    // 3. Simpan dosen baru yang dipilih admin
    // ──────────────────────────────────────────────────────

    public function simpan(Request $request)
    {
        $request->validate([
            'dosen'                  => 'required|array|min:1',
            'dosen.*.nama'           => 'required|string|max:200',
            'dosen.*.email'          => 'required|email|max:200|unique:users,email',
            'dosen.*.nip'            => 'nullable|string|max:50',
            'dosen.*.program_studi'  => 'nullable|string|max:100',
        ], [
            'dosen.required'         => 'Pilih minimal satu dosen untuk didaftarkan.',
            'dosen.*.email.required' => 'Email wajib diisi untuk setiap dosen.',
            'dosen.*.email.unique'   => 'Email :input sudah digunakan.',
        ]);

        $berhasil = 0;
        $gagal    = [];
        $passwordDefault = 'dosen123';

        foreach ($request->dosen as $idx => $data) {
            // Cek ulang apakah email sudah ada (race condition guard)
            if (User::where('email', $data['email'])->exists()) {
                $gagal[] = "{$data['nama']} — email {$data['email']} sudah digunakan.";
                continue;
            }

            User::create([
                'name'          => trim($data['nama']),
                'email'         => strtolower(trim($data['email'])),
                'password'      => Hash::make($passwordDefault),
                'role'          => 'dosen',
                'nip_nim'       => trim($data['nip'] ?? ''),
                'program_studi' => trim($data['program_studi'] ?? ''),
                'is_active'     => true,
            ]);

            $berhasil++;
        }

        if (!empty($gagal)) {
            $pesanGagal = implode('; ', $gagal);
            return redirect()->route('admin.users.index')
                ->with('warning',
                    "<strong>{$berhasil}</strong> dosen berhasil ditambahkan. " .
                    count($gagal) . " gagal: {$pesanGagal}"
                );
        }

        return redirect()->route('admin.users.index')
            ->with('success',
                "<strong>{$berhasil}</strong> dosen berhasil ditambahkan. " .
                "Password default: <code>{$passwordDefault}</code> — " .
                "minta dosen untuk mengganti password setelah login pertama."
            );
    }

    // ──────────────────────────────────────────────────────
    // Helper: bersihkan nama (hilangkan newline, spasi ganda)
    // ──────────────────────────────────────────────────────

    private function bersihkanNama(string $nama): string
    {
        // Hilangkan newline yang mungkin ada di tengah nama (artefak Excel)
        $nama = preg_replace('/[\r\n]+/', ' ', $nama);
        $nama = preg_replace('/\s+/', ' ', $nama);
        $nama = trim($nama);

        // Abaikan baris yang bukan nama (misal: "TBA", "LPPM UNSRAT", "Prodi", "-")
        $abaikan = ['tba', 'lppm unsrat', 'prodi', '-', ''];
        if (in_array(strtolower($nama), $abaikan)) return '';

        // Minimal 3 karakter
        if (strlen($nama) < 3) return '';

        return $nama;
    }

    // ──────────────────────────────────────────────────────
    // Helper: cari kecocokan dosen di database
    // Mengembalikan: ['tepat' => User|null, 'mirip' => [User, ...]]
    // ──────────────────────────────────────────────────────

    private function cariMatch(string $namaExcel, $semuaDosen): array
    {
        $namaLower = strtolower($namaExcel);
        $tepat     = null;
        $mirip     = [];

        foreach ($semuaDosen as $dosen) {
            $dbLower = strtolower($dosen->name);

            // 1. Exact match
            if ($dbLower === $namaLower) {
                return ['tepat' => $dosen, 'mirip' => []];
            }

            // 2. DB mengandung nama dari Excel (atau sebaliknya)
            if (str_contains($dbLower, $namaLower) || str_contains($namaLower, $dbLower)) {
                $tepat = $dosen;
                break;
            }

            // 3. Kesamaan nama depan + nama belakang (abaikan gelar)
            $namaIntiExcel = $this->ambilNamaInti($namaExcel);
            $namaIntiDb    = $this->ambilNamaInti($dosen->name);
            if ($namaIntiExcel && $namaIntiDb && $namaIntiExcel === $namaIntiDb) {
                $tepat = $dosen;
                break;
            }

            // 4. Similarity ≥ 80% → tandai sebagai mirip
            similar_text($namaLower, $dbLower, $persen);
            if ($persen >= 80) {
                $mirip[] = $dosen;
            }
        }

        return ['tepat' => $tepat, 'mirip' => $mirip];
    }

    /**
     * Ambil inti nama: hilangkan gelar akademik umum
     * "Dr.Eng. Sary D. E. Paturusi, ST, M.Eng" → "sary d. e. paturusi"
     */
    private function ambilNamaInti(string $nama): string
    {
        // Hilangkan gelar di depan
        $nama = preg_replace('/^(Dr\.Eng\.|Dr\.|Ir\.|Prof\.|Drs\.|Pdt\.|Pst\.|Ws\.)\s*/i', '', $nama);
        // Hilangkan gelar di belakang koma
        $nama = preg_replace('/,\s*(ST|MT|M\.Kom|M\.Eng|M\.Cs|M\.Sc|MTI|Ph\.D|S\.Kom|S\.Pd|SH|MTh|Pr\.|M\.Kom\.)[^,]*/i', '', $nama);
        return strtolower(trim($nama));
    }
}
