<?php

namespace App\Http\Controllers;

use App\Models\JadwalTetap;
use App\Models\RuangKelas;
use App\Models\User;
use App\Services\SlotWaktuMapper;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JadwalImportController extends Controller
{
    // ── Halaman form import ──────────────────────────────
    public function formImport()
    {
        $slots     = SlotWaktuMapper::semuaSlot();
        $ruangList = RuangKelas::aktif()->orderBy('kode_ruang')->get();
        $dosenList = User::dosen()->orderBy('name')->get();

        return view('admin.jadwal.import-excel', compact('slots', 'ruangList', 'dosenList'));
    }

    // ── Proses import Excel ──────────────────────────────
    public function prosesImport(Request $request)
    {
        $request->validate([
            'file_excel'            => 'required|file|mimes:xlsx,xls|max:5120',
            'tahun_akademik'        => 'required|string',
            'semester_ganjil_genap' => 'required|in:ganjil,genap',
            'program_studi'         => 'nullable|string|max:100',
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
            return back()->with('error', 'Gagal membaca file Excel: '.$e->getMessage());
        }

        $berhasil    = 0;
        $gagal       = [];
        $dilewati    = 0;
        $barisHeader = null;

        // Mapping header kolom
        $kolomMap = [];
        $hariList = ['senin','selasa','rabu','kamis','jumat','sabtu','sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

        foreach ($rows as $noRow => $row) {
            // Deteksi baris header — lebih fleksibel, cari baris yang punya minimal 3 kolom berisi teks
            if ($barisHeader === null) {
                $rowValues = array_filter(array_map(fn($v) => strtolower(trim((string)$v)), $row));
                $jumlahTeks = count($rowValues);
                // Deteksi apakah baris ini adalah header (ada kata kunci jadwal)
                $kataKunciHeader = ['kode','nama','mk','mata','kuliah','hari','dosen','pengajar','ruang','slot','waktu','jam','sks','kelas','semester','prodi','program'];
                $cocok = 0;
                foreach ($rowValues as $v) {
                    foreach ($kataKunciHeader as $kk) {
                        if (str_contains($v, $kk)) { $cocok++; break; }
                    }
                }
                if ($jumlahTeks >= 3 && $cocok >= 3) {
                    $barisHeader = $noRow;
                    $nextColIsSemester = false;
                    foreach ($row as $col => $val) {
                        $valLower = strtolower(trim((string)$val));

                        // FIX: "SKS Semester" digabung → kolom berikutnya (None) = semester
                        if ($nextColIsSemester && !isset($kolomMap['semester'])) {
                            $kolomMap['semester'] = $col;
                            $nextColIsSemester    = false;
                        }

                        if (empty($valLower)) continue;

                        if ((str_contains($valLower, 'kode') && str_contains($valLower, 'mk')) || $valLower === 'kode') {
                            $kolomMap['kode_mk'] = $col;
                        }
                        if (str_contains($valLower, 'nama') && (str_contains($valLower, 'mk') || str_contains($valLower, 'kuliah'))) {
                            $kolomMap['mata_kuliah'] = $col;
                        } elseif (str_contains($valLower, 'mata') && str_contains($valLower, 'kuliah')) {
                            $kolomMap['mata_kuliah'] = $col;
                        } elseif ($valLower === 'nama') {
                            $kolomMap['mata_kuliah'] = $col;
                        }
                        if ($valLower === 'kelas')                  $kolomMap['kelas']         = $col;
                        if (str_contains($valLower, 'pengajar') || str_contains($valLower, 'dosen'))
                                                                    $kolomMap['pengajar']      = $col;
                        if ($valLower === 'hari')                   $kolomMap['hari']          = $col;
                        if (str_contains($valLower, 'slot') || str_contains($valLower, 'waktu'))
                                                                    $kolomMap['slot_waktu']    = $col;
                        if (str_contains($valLower, 'jam') && str_contains($valLower, 'mulai'))
                                                                    $kolomMap['jam_mulai']     = $col;
                        if (str_contains($valLower, 'jam') && str_contains($valLower, 'selesai'))
                                                                    $kolomMap['jam_selesai']   = $col;
                        if (str_contains($valLower, 'ruang'))       $kolomMap['ruang']         = $col;
                        if (str_contains($valLower, 'prodi') || str_contains($valLower, 'program'))
                                                                    $kolomMap['program_studi'] = $col;
                        if ($valLower === 'sks') {
                            $kolomMap['sks'] = $col;
                        } elseif ($valLower === 'semester') {
                            $kolomMap['semester'] = $col;
                        } elseif (str_contains($valLower, 'sks') && str_contains($valLower, 'semester')) {
                            // "SKS Semester" di satu sel → semester ada di kolom berikutnya
                            $kolomMap['sks']   = $col;
                            $nextColIsSemester = true;
                        }
                    }
                    // Jika tidak ada kolom 'mata_kuliah', coba kolom 'nama' saja
                    if (!isset($kolomMap['mata_kuliah'])) {
                        foreach ($row as $col => $val) {
                            $valLower = strtolower(trim((string)$val));
                            if ($valLower === 'nama') { $kolomMap['mata_kuliah'] = $col; break; }
                        }
                    }
                }
                continue;
            }

            // Lewati baris kosong
            $rowValues = array_filter(array_map('trim', array_map('strval', $row)));
            if (empty($rowValues)) { $dilewati++; continue; }

            $noData = $noRow - $barisHeader;

            // Ambil nilai tiap kolom
            $kodeMk     = $this->ambil($row, $kolomMap, 'kode_mk');
            $mataKuliah = $this->ambil($row, $kolomMap, 'mata_kuliah');
            $sks        = $this->ambil($row, $kolomMap, 'sks');
            $semester   = $this->ambil($row, $kolomMap, 'semester');
            $kelas      = $this->ambil($row, $kolomMap, 'kelas');

            // Fallback: jika semester kosong, cek kolom tepat setelah SKS
            if (empty($semester) && isset($kolomMap['sks'])) {
                $kolomKeys = array_keys($row);
                $idxSks    = array_search($kolomMap['sks'], $kolomKeys);
                if ($idxSks !== false && isset($kolomKeys[$idxSks + 1])) {
                    $nextVal = trim((string)($row[$kolomKeys[$idxSks + 1]] ?? ''));
                    if (is_numeric($nextVal) && (int)$nextVal >= 1 && (int)$nextVal <= 8) {
                        $semester = $nextVal;
                    }
                }
            }
            $pengajar    = $this->ambil($row, $kolomMap, 'pengajar');
            $hariRaw     = $this->ambil($row, $kolomMap, 'hari');
            $slotWaktu   = $this->ambil($row, $kolomMap, 'slot_waktu');
            $ruangRaw    = $this->ambil($row, $kolomMap, 'ruang');
            $prodiRaw    = $this->ambil($row, $kolomMap, 'program_studi');

            // Skip jika mata kuliah kosong
            if (empty($mataKuliah)) { $dilewati++; continue; }

            // Skip jika ruang TBA / belum ditentukan — ini normal, bukan error
            $ruangTrimmed = trim($ruangRaw);
            if (empty($ruangTrimmed) || in_array(strtoupper($ruangTrimmed), ['TBA', 'TBD', '-', 'NONE', '?'])) {
                $dilewati++;
                continue;
            }

            // Validasi hari
            $hariLower = strtolower(trim($hariRaw));
            $hariMap   = [
                'senin'=>'senin','selasa'=>'selasa','rabu'=>'rabu','kamis'=>'kamis','jumat'=>'jumat','sabtu'=>'sabtu',
                'monday'=>'senin','tuesday'=>'selasa','wednesday'=>'rabu','thursday'=>'kamis','friday'=>'jumat','saturday'=>'sabtu',
            ];
            if (!isset($hariMap[$hariLower])) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' — Hari '{$hariRaw}' tidak valid.";
                continue;
            }
            $hari = $hariMap[$hariLower];

            // Parse slot waktu → jam
            // FIX: jika kolom slot_waktu ada, gunakan. Jika tidak, coba kolom jam_mulai & jam_selesai langsung
            $waktu = null;
            if (!empty($slotWaktu)) {
                $waktu = SlotWaktuMapper::parse((string)$slotWaktu);
                // Fallback: coba gabungkan sebagai "HH:MM - HH:MM"
                if (!$waktu) {
                    $jamMulaiLangsung  = $this->ambil($row, $kolomMap, 'jam_mulai');
                    $jamSelesaiLangsung = $this->ambil($row, $kolomMap, 'jam_selesai');
                    if ($jamMulaiLangsung && $jamSelesaiLangsung) {
                        $waktu = SlotWaktuMapper::parse("{$jamMulaiLangsung} - {$jamSelesaiLangsung}");
                    }
                }
            } else {
                // Tidak ada kolom slot_waktu: coba kolom jam langsung
                $jamMulaiLangsung   = $this->ambil($row, $kolomMap, 'jam_mulai');
                $jamSelesaiLangsung = $this->ambil($row, $kolomMap, 'jam_selesai');
                if ($jamMulaiLangsung && $jamSelesaiLangsung) {
                    $waktu = SlotWaktuMapper::parse("{$jamMulaiLangsung} - {$jamSelesaiLangsung}");
                } elseif ($jamMulaiLangsung) {
                    $waktu = SlotWaktuMapper::parse($jamMulaiLangsung);
                }
            }

            if (!$waktu) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' — Slot waktu '{$slotWaktu}' tidak dikenali. Gunakan format '1 - 2' atau '08:00 - 10:30'.";
                continue;
            }

            // Cari ruang
            // Normalisasi format ruang — "JTE - 04", "JTE-04", "JTE -04" → semua jadi "JTE-04"
            $kodeRuangNorm     = strtoupper(preg_replace('/\s*[-–]\s*/', '-', trim($ruangRaw)));
            $kodeRuangNormLow  = strtolower($kodeRuangNorm);
            $ruang = RuangKelas::aktif()->get()->first(function ($r) use ($kodeRuangNormLow) {
                $dbNorm = strtolower(preg_replace('/\s*[-–]\s*/', '-', $r->kode_ruang));
                return $dbNorm === $kodeRuangNormLow;
            });

            // FIX: auto-create ruang jika belum ada di database (opsi baru)
            if (!$ruang && $request->boolean('auto_create_ruang', false)) {
                // Deteksi jenis ruang dari kode
                $jenisRuang = str_contains(strtolower($kodeRuangNorm), 'kdk') ? 'laboratorium' : 'kelas';
                // Deteksi gedung dari bagian sebelum tanda hubung
                $gedung = explode('-', $kodeRuangNorm)[0] ?? $kodeRuangNorm;
                $ruang = RuangKelas::create([
                    'kode_ruang' => $kodeRuangNorm,
                    'nama_ruang' => 'Ruang ' . $kodeRuangNorm,
                    'gedung'     => $gedung,
                    'lantai'     => 0,
                    'kapasitas'  => 40,
                    'jenis'      => $jenisRuang,
                    'fasilitas'  => ['proyektor', 'whiteboard', 'ac'],
                    'status'     => 'aktif',
                ]);
            }

            if (!$ruang) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' — Ruang '{$ruangRaw}' tidak ditemukan di database.";
                continue;
            }

            // Cari dosen — ambil dosen PERTAMA jika ada lebih dari satu (dipisah /)
            $namaDosen = $this->ambilDosenPertama($pengajar);
            $dosen     = $this->cariDosen($namaDosen);

            if (!$dosen) {
                // Coba ambil dosen kedua
                $namaDosen2 = $this->ambilDosenKedua($pengajar);
                if ($namaDosen2) $dosen = $this->cariDosen($namaDosen2);
            }

            if (!$dosen) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' — Dosen '{$pengajar}' tidak ditemukan. ".
                           "Pastikan dosen sudah terdaftar dengan nama yang sama persis.";
                continue;
            }

            // Program studi
            $prodi = !empty($prodiRaw) ? $prodiRaw : ($request->program_studi ?? 'Teknik Informatika');

            // Cek konflik ruang
            if ($this->adaKonflikRuang($ruang->id, $hari, $waktu['jam_mulai'], $waktu['jam_selesai'],
                $request->tahun_akademik, $request->semester_ganjil_genap)) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' kelas {$kelas} — Ruang {$ruang->kode_ruang} bentrok pada {$hari} slot {$slotWaktu}.";
                continue;
            }

            // Cek konflik dosen
            if ($this->adaKonflikDosen($dosen->id, $hari, $waktu['jam_mulai'], $waktu['jam_selesai'],
                $request->tahun_akademik, $request->semester_ganjil_genap)) {
                $gagal[] = "Baris {$noData}: '{$mataKuliah}' — Dosen {$dosen->name} bentrok pada {$hari} slot {$slotWaktu}.";
                continue;
            }

            // Simpan ke database
            JadwalTetap::create([
                'ruang_kelas_id'        => $ruang->id,
                'dosen_id'              => $dosen->id,
                'mata_kuliah'           => trim($mataKuliah),
                'kode_mk'               => trim($kodeMk) ?: null,
                'kelas'                 => trim($kelas) ?: 'A',
                'program_studi'         => $prodi,
                'semester'              => (int) $semester ?: 1,
                'sks'                   => (int) $sks ?: 2,
                'tahun_akademik'        => $request->tahun_akademik,
                'semester_ganjil_genap' => $request->semester_ganjil_genap,
                'hari'                  => $hari,
                'jam_mulai'             => $waktu['jam_mulai'],
                'jam_selesai'           => $waktu['jam_selesai'],
                'status'                => 'aktif',
            ]);

            $berhasil++;
        }

        // Simpan error ke session untuk ditampilkan
        $jumlahGagal    = count($gagal);
        $pesan = "Import selesai: <strong>{$berhasil}</strong> jadwal berhasil";
        if ($dilewati > 0) $pesan .= ", <strong>{$dilewati}</strong> dilewati (TBA/kosong)";
        $pesan .= ".";

        if ($jumlahGagal > 0) {
            $pesan .= " <strong>{$jumlahGagal}</strong> baris gagal — lihat detail di bawah.";
            return redirect()->route('admin.jadwal.index')
                ->with('warning', $pesan)
                ->with('import_errors', $gagal);
        }

        return redirect()->route('admin.jadwal.index')
            ->with('success', $pesan);
    }

    // ── Download Template Excel ──────────────────────────
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal');

        // ── Header utama ─────────────────────────────────
        $headers = ['Kode MK','Nama MK','SKS','Semester','Kelas','Pengajar','Hari','Slot Waktu','Ruang','Program Studi'];
        $cols    = ['A','B','C','D','E','F','G','H','I','J'];

        foreach ($headers as $i => $h) {
            $cell = $cols[$i].'1';
            $sheet->setCellValue($cell, $h);
        }

        // Style header
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Contoh data ──────────────────────────────────
        $contoh = [
            ['TIK1021','Pancasila',           2, 1, 'A', 'Ir. S. T. G. Kaunang, MT, Ph.D.',              'KAMIS',  '4 - 5',  'JTE-04', 'Teknik Informatika'],
            ['TIK1021','Pancasila',           2, 1, 'B', 'Nancy J. Tuturoong, ST, M.Kom.',                'KAMIS',  '4 - 5',  'JTE-05', 'Teknik Informatika'],
            ['TIK1031','Bahasa Indonesia',    2, 1, 'A', 'Dr.Eng. Sary D. E. Paturusi, ST, M.Eng',       'SELASA', '3 - 4',  'JTE-04', 'Teknik Informatika'],
            ['TIK1031','Bahasa Indonesia',    2, 1, 'B', 'Fransisca J. Pontoh, ST, MT.',                  'SELASA', '3 - 4',  'JTE-05', 'Teknik Informatika'],
            ['TIK1101','Pendidikan Agama',    2, 1, 'A', 'Pdt. Dina Pontoh, MTh.',                        'JUMAT',  '1 - 2',  'JTE-04', 'Teknik Informatika'],
            ['TIK2001','Kalkulus',            3, 2, 'A', 'Prof. Dr. Nama Dosen, M.Sc.',                   'SENIN',  '1 - 3',  'JTE-01', 'Teknik Informatika'],
            ['TIK3001','Pemrograman Web',     3, 5, 'A', 'Dr. Nama Dosen, M.Kom.',                        'SELASA', '6 - 8',  'LAB-A',  'Teknik Informatika'],
        ];

        $rowColors = ['F0F9FF','FFFFFF']; // Alternating row colors
        foreach ($contoh as $i => $data) {
            $rowNum = $i + 2;
            foreach ($data as $j => $val) {
                $sheet->setCellValue($cols[$j].$rowNum, $val);
            }
            $bgColor = $rowColors[$i % 2];
            $sheet->getStyle('A'.$rowNum.':J'.$rowNum)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
            ]);
        }

        // ── Lebar kolom ──────────────────────────────────
        $widths = [12, 35, 6, 10, 8, 45, 10, 12, 12, 25];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($cols[$i])->setWidth($w);
        }

        // ── Sheet 2: Referensi slot waktu ─────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Referensi Slot');
        $sheet2->setCellValue('A1', 'Slot');
        $sheet2->setCellValue('B1', 'Jam Mulai');
        $sheet2->setCellValue('C1', 'Jam Selesai');
        $sheet2->setCellValue('D1', 'Keterangan');
        $sheet2->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
        ]);

        $slots = SlotWaktuMapper::semuaSlot();
        $row   = 2;
        foreach ($slots as $slot => $jam) {
            $sheet2->setCellValue('A'.$row, $slot);
            $sheet2->setCellValue('B'.$row, $jam['mulai']);
            $sheet2->setCellValue('C'.$row, $jam['selesai']);
            $sheet2->setCellValue('D'.$row, "Slot {$slot} ({$jam['mulai']} – {$jam['selesai']})");
            $row++;
        }
        foreach (['A','B','C','D'] as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Sheet 3: Petunjuk pengisian ───────────────────
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Petunjuk');
        $petunjuk = [
            ['PETUNJUK PENGISIAN IMPORT JADWAL', ''],
            ['', ''],
            ['Kolom', 'Keterangan'],
            ['Kode MK',        'Kode mata kuliah (opsional), contoh: TIK1021'],
            ['Nama MK',        'Nama mata kuliah (WAJIB)'],
            ['SKS',            'Jumlah SKS, contoh: 2 atau 3'],
            ['Semester',       'Semester 1-8'],
            ['Kelas',          'Huruf kelas: A, B, C, dst'],
            ['Pengajar',       'Nama dosen HARUS sama dengan yang terdaftar di sistem. Jika 2 dosen, pisahkan dengan /'],
            ['Hari',           'SENIN / SELASA / RABU / KAMIS / JUMAT / SABTU (huruf besar atau kecil)'],
            ['Slot Waktu',     'Format: "1 - 2" atau "4-5" (lihat sheet Referensi Slot untuk detail jam)'],
            ['Ruang',          'Kode ruang HARUS sama dengan yang terdaftar di sistem, contoh: JTE-04'],
            ['Program Studi',  'Nama program studi (opsional jika diisi di form)'],
            ['', ''],
            ['CATATAN PENTING:', ''],
            ['1', 'Baris pertama sheet utama HARUS berisi header kolom'],
            ['2', 'Ruang "TBA" atau kosong akan dilewati (tidak diimport)'],
            ['3', 'Jika ada konflik jadwal (ruang atau dosen bentrok), baris tersebut akan dilewati'],
            ['4', 'Satu dosen tidak bisa mengajar di 2 ruang pada waktu yang sama'],
        ];

        foreach ($petunjuk as $i => $p) {
            $sheet3->setCellValue('A'.($i+1), $p[0]);
            $sheet3->setCellValue('B'.($i+1), $p[1]);
        }
        $sheet3->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 13]]);
        $sheet3->getStyle('A3:B3')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']]]);
        $sheet3->getColumnDimension('A')->setWidth(20);
        $sheet3->getColumnDimension('B')->setWidth(70);

        // Aktifkan sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        // Output
        $writer   = new Xlsx($spreadsheet);
        $filename = 'template-import-jadwal.xlsx';

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'max-age=0');
    }

    // ── Helper methods ───────────────────────────────────

    private function ambil(array $row, array $map, string $key): string
    {
        if (!isset($map[$key])) return '';
        return trim((string)($row[$map[$key]] ?? ''));
    }

    /**
     * Ambil nama dosen pertama dari string seperti:
     * "Dr. Budi / Siti Rahayu"  → "Dr. Budi"
     * "Dr. Budi, M.Kom."        → "Dr. Budi, M.Kom."
     */
    private function ambilDosenPertama(string $pengajar): string
    {
        $parts = preg_split('/\s*\/\s*/', $pengajar);
        return trim($parts[0] ?? $pengajar);
    }

    private function ambilDosenKedua(string $pengajar): ?string
    {
        $parts = preg_split('/\s*\/\s*/', $pengajar);
        return isset($parts[1]) ? trim($parts[1]) : null;
    }

    /**
     * Cari dosen berdasarkan nama — fuzzy match
     * Coba cocokkan substring dari nama dosen di database
     */
    private function cariDosen(string $namaDosen): ?User
    {
        if (empty($namaDosen)) return null;

        // 1. Exact match
        $dosen = User::dosen()->where('name', $namaDosen)->first();
        if ($dosen) return $dosen;

        // 2. Normalisasi: hapus titik ganda, spasi berlebih, titik di akhir
        // Menangani variasi: "Ph.D" vs "Ph.D." vs "M.Eng," vs "M.Eng."
        $normalisasi = fn(string $s) => trim(preg_replace([
            '/\.{2,}/',        // titik berulang
            '/\s+/',           // spasi ganda
            '/\.$/',           // titik di akhir string
            '/,\s*$/',         // koma di akhir
        ], ['.', ' ', '', ''], $s));

        $namaNorm = $normalisasi($namaDosen);
        $dosen    = User::dosen()->get()->first(
            fn($u) => $normalisasi($u->name) === $namaNorm
        );
        if ($dosen) return $dosen;

        // 3. Nama inti saja (tanpa semua gelar depan & belakang)
        // "Dr.Eng. Ir. Vecky C. Poekoel, ST, MT." → "Vecky C. Poekoel"
        $namaInti = $this->ambilNamaIntiDosen($namaDosen);
        if ($namaInti && strlen($namaInti) > 4) {
            // Cari di DB yang namanya mengandung nama inti
            $dosen = User::dosen()
                ->where('name', 'like', '%'.$namaInti.'%')
                ->first();
            if ($dosen) return $dosen;

            // Cari dengan soundex/levenshtein untuk typo ringan
            // (misal: "Ruindengan" vs "Ruindungan")
            $dosenList = User::dosen()->get();
            foreach ($dosenList as $d) {
                $namaIntiDb = $this->ambilNamaIntiDosen($d->name);
                if (strlen($namaIntiDb) > 4 && levenshtein(
                    strtolower($namaInti),
                    strtolower($namaIntiDb)
                ) <= 3) {
                    return $d;
                }
            }
        }

        // 4. Nama sebelum koma pertama (nama + gelar depan saja)
        $namaDepan = trim(explode(',', $namaDosen)[0]);
        // Hapus gelar depan
        $namaDepan = trim(preg_replace('/^(Dr\.Eng\.|Dr\.|Ir\.|Prof\.|Drs\.|Dra\.|Pdt\.|Pst\.|Ws\.|H\.|Hj\.)\s*/i', '', $namaDepan));
        if (strlen($namaDepan) > 4) {
            $dosen = User::dosen()
                ->where('name', 'like', '%'.$namaDepan.'%')
                ->first();
            if ($dosen) return $dosen;
        }

        return null;
    }

    /**
     * Ambil nama inti dosen — hilangkan gelar depan dan belakang
     * "Dr.Eng. Ir. Vecky C. Poekoel, ST, MT." → "Vecky C. Poekoel"
     * "Pdt. Dina Pontoh, MTh." → "Dina Pontoh"
     */
    private function ambilNamaIntiDosen(string $nama): string
    {
        // Hapus gelar di depan
        $nama = preg_replace(
            '/^(Dr\.Eng\.|Dr\.|Ir\.|Prof\.|Drs\.|Dra\.|Pdt\.|Pst\.|Ws\.|Ns\.|H\.|Hj\.)\s*/i',
            '', $nama
        );
        $nama = preg_replace('/^(Ir\.|Dr\.)\s*/i', '', $nama); // double gelar
        // Hapus gelar di belakang koma
        $nama = preg_replace('/,.*$/', '', $nama);
        return trim($nama);
    }

    private function adaKonflikRuang(int $ruangId, string $hari, string $jamMulai, string $jamSelesai, string $tahun, string $semester): bool
    {
        return JadwalTetap::where('ruang_kelas_id', $ruangId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where('status', 'aktif')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->exists();
    }

    private function adaKonflikDosen(int $dosenId, string $hari, string $jamMulai, string $jamSelesai, string $tahun, string $semester): bool
    {
        return JadwalTetap::where('dosen_id', $dosenId)
            ->where('hari', $hari)
            ->where('tahun_akademik', $tahun)
            ->where('semester_ganjil_genap', $semester)
            ->where('status', 'aktif')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->exists();
    }
}