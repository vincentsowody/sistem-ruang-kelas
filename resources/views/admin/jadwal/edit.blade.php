@extends('layouts.app')
@section('title', 'Edit Jadwal Tetap')
@section('page_title', 'Edit Jadwal')
@section('page_subtitle', 'Perbarui data jadwal')

@section('content')

<div class="mb-5">
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-3">
        <a href="{{ route('admin.jadwal.index') }}" class="hover:text-blue-600">Jadwal Tetap</a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
        <span class="text-slate-800 font-medium">Edit Jadwal</span>
    </div>
    <h1 class="text-lg font-bold text-slate-800">Edit Jadwal — {{ $jadwal->mata_kuliah }}</h1>
</div>

<div class="max-w-2xl w-full">
    <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}" class="space-y-5" id="formJadwal">
        @csrf
        @method('PUT')
        {{-- BUG FIX D: ID jadwal ini agar tidak dianggap konflik dengan dirinya sendiri --}}
        <input type="hidden" id="kecualiId" value="{{ $jadwal->id }}">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">
            <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-book text-blue-500"></i> Informasi Mata Kuliah
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Mata Kuliah <span class="text-red-500">*</span></label>
                    <input type="text" name="mata_kuliah" value="{{ old('mata_kuliah', $jadwal->mata_kuliah) }}"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $errors->has('mata_kuliah') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode MK</label>
                    <input type="text" name="kode_mk" value="{{ old('kode_mk', $jadwal->kode_mk) }}"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="kelas" value="{{ old('kelas', $jadwal->kelas) }}"
                        class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border-slate-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester <span class="text-red-500">*</span></label>
                    <select name="semester" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        @for($i=1;$i<=8;$i++)
                        <option value="{{ $i }}" {{ old('semester',$jadwal->semester)==$i?'selected':'' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">SKS <span class="text-red-500">*</span></label>
                    <select name="sks" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        @for($i=1;$i<=6;$i++)
                        <option value="{{ $i }}" {{ old('sks',$jadwal->sks)==$i?'selected':'' }}>{{ $i }} SKS</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Program Studi <span class="text-red-500">*</span></label>
                <input type="text" name="program_studi" value="{{ old('program_studi', $jadwal->program_studi) }}"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Dosen Pengampu <span class="text-red-500">*</span></label>
                <select name="dosen_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    @foreach($dosenList as $d)
                    <option value="{{ $d->id }}" {{ old('dosen_id',$jadwal->dosen_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">
            <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-clock text-green-500"></i> Waktu & Ruangan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Akademik</label>
                    <input type="text" name="tahun_akademik" value="{{ old('tahun_akademik', $jadwal->tahun_akademik) }}" id="tahunAkademik"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester</label>
                    <select name="semester_ganjil_genap" id="semesterGG" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option value="ganjil" {{ old('semester_ganjil_genap',$jadwal->semester_ganjil_genap)=='ganjil'?'selected':'' }}>Ganjil</option>
                        <option value="genap"  {{ old('semester_ganjil_genap',$jadwal->semester_ganjil_genap)=='genap'?'selected':'' }}>Genap</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Hari <span class="text-red-500">*</span></label>
                    <select name="hari" id="hariInput" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white" onchange="cekKonflik()">
                        @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $h)
                        <option value="{{ $h }}" {{ old('hari',$jadwal->hari)==$h?'selected':'' }}>{{ ucfirst($h) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', substr($jadwal->jam_mulai,0,5)) }}" id="jamMulai"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="cekKonflik()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', substr($jadwal->jam_selesai,0,5)) }}" id="jamSelesai"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="cekKonflik()">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ruang Kelas <span class="text-red-500">*</span></label>
                <select name="ruang_kelas_id" id="ruangInput" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white" onchange="cekKonflik()">
                    @foreach($ruangList as $r)
                    <option value="{{ $r->id }}" {{ old('ruang_kelas_id',$jadwal->ruang_kelas_id)==$r->id?'selected':'' }}>
                        {{ $r->kode_ruang }} — {{ $r->nama_ruang }} ({{ $r->kapasitas }} kursi)
                    </option>
                    @endforeach
                </select>
                <div id="konflikInfo" class="mt-2 hidden"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="aktif" {{ old('status',$jadwal->status)=='aktif'?'checked':'' }} class="text-blue-600">
                        <span class="text-sm text-slate-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="nonaktif" {{ old('status',$jadwal->status)=='nonaktif'?'checked':'' }} class="text-blue-600">
                        <span class="text-sm text-slate-700">Nonaktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" id="btnSimpan"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition flex items-center gap-2 text-sm w-full sm:w-auto justify-center">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.jadwal.index') }}"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-6 py-2.5 rounded-xl transition text-sm w-full sm:w-auto text-center">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
// BUG FIX D: kecuali_id sudah dikirim lewat hidden input #kecualiId
// BUG FIX B: tombol di-reset setiap kali input berubah
let konflikTimer = null;

function cekKonflik() {
    clearTimeout(konflikTimer);

    // Reset tombol setiap perubahan input
    const btnSimpan = document.getElementById('btnSimpan');
    btnSimpan.disabled = false;
    btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');

    konflikTimer = setTimeout(() => {
        const ruangId    = document.getElementById('ruangInput').value;
        const hari       = document.getElementById('hariInput').value;
        const jamMulai   = document.getElementById('jamMulai').value;
        const jamSelesai = document.getElementById('jamSelesai').value;
        const tahun      = document.getElementById('tahunAkademik').value;
        const semGG      = document.getElementById('semesterGG').value;
        const dosenId    = document.getElementById('dosenInput')?.value || '';
        const kecualiId  = document.getElementById('kecualiId')?.value || '';
        const info       = document.getElementById('konflikInfo');

        if (!ruangId || !hari || !jamMulai || !jamSelesai) {
            info.classList.add('hidden');
            return;
        }

        if (jamSelesai <= jamMulai) {
            info.innerHTML = `<div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 mt-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xs"></i>
                <p class="text-amber-700 text-xs font-medium">Jam selesai harus lebih dari jam mulai.</p>
            </div>`;
            info.classList.remove('hidden');
            btnSimpan.disabled = true;
            btnSimpan.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        info.innerHTML = `<div class="flex items-center gap-2 px-3 py-2 mt-2">
            <i class="fa-solid fa-spinner fa-spin text-slate-400 text-xs"></i>
            <p class="text-slate-400 text-xs">Memeriksa konflik jadwal...</p>
        </div>`;
        info.classList.remove('hidden');

        const params = {
            ruang_kelas_id: ruangId, hari,
            jam_mulai: jamMulai, jam_selesai: jamSelesai,
            tahun_akademik: tahun, semester_ganjil_genap: semGG,
        };
        if (dosenId)   params.dosen_id   = dosenId;
        if (kecualiId) params.kecuali_id = kecualiId;

        fetch(`{{ route('api.jadwal.cek-konflik') }}?` + new URLSearchParams(params), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.konflik) {
                info.innerHTML = `<div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-3 py-2.5 mt-2">
                    <i class="fa-solid fa-circle-xmark text-red-500 text-xs mt-0.5 shrink-0"></i>
                    <p class="text-red-700 text-xs leading-relaxed">${data.detail}</p>
                </div>`;
                btnSimpan.disabled = true;
                btnSimpan.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                info.innerHTML = `<div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-xl px-3 py-2 mt-2">
                    <i class="fa-solid fa-circle-check text-green-500 text-xs"></i>
                    <p class="text-green-700 text-xs">${data.detail}</p>
                </div>`;
                btnSimpan.disabled = false;
                btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        })
        .catch(() => {
            info.classList.add('hidden');
        });
    }, 600);
}
</script>
@endsection