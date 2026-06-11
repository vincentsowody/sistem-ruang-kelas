{{--
  Partial: satu kartu data dosen

  Params:
    $i
    $nama
    $email
    $nip
    $prodi
--}}

<div class="flex items-start justify-between mb-4">

  <div class="flex items-center gap-2">
    <span class="bg-blue-100 text-blue-600 text-xs font-bold px-2.5 py-1 rounded-lg dosen-number">
      Dosen
    </span>

    @if(is_numeric($i) && (int)$i === 0)
      <span class="text-xs text-gray-400">(wajib)</span>
    @endif
  </div>

  <button
      type="button"
      onclick="hapusKartu('{{ $i }}')"
      class="text-gray-300 hover:text-red-500 transition"
      title="Hapus dosen ini">
    <i class="fa-solid fa-xmark text-lg"></i>
  </button>

</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

  {{-- Nama --}}
  <div class="sm:col-span-2">
    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
      Nama Lengkap <span class="text-red-500">*</span>
    </label>

    <input
      type="text"
      name="dosen[{{ $i }}][nama]"
      value="{{ $nama }}"
      placeholder="Nama lengkap beserta gelar akademik"
      required
      class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

    @error("dosen.{$i}.nama")
      <p class="text-red-500 text-xs mt-1">
        <i class="fa-solid fa-circle-xmark mr-1"></i>{{ $message }}
      </p>
    @enderror
  </div>

  {{-- Email --}}
  <div>
    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
      Email <span class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input
        type="email"
        name="dosen[{{ $i }}][email]"
        value="{{ $email }}"
        placeholder="email@kampus.ac.id"
        required
        oninput="validasiEmail(this)"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 pl-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

      <i class="fa-solid fa-envelope absolute left-3 top-3 text-gray-300 text-sm"></i>
    </div>

    @error("dosen.{$i}.email")
      <p class="text-red-500 text-xs mt-1">
        <i class="fa-solid fa-circle-xmark mr-1"></i>{{ $message }}
      </p>
    @enderror
  </div>

  {{-- NIP --}}
  <div>
    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
      NIP / NIDN
    </label>

    <div class="relative">
      <input
        type="text"
        name="dosen[{{ $i }}][nip]"
        value="{{ $nip }}"
        placeholder="Nomor Induk Pegawai/Dosen"
        maxlength="50"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 pl-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

      <i class="fa-solid fa-id-card absolute left-3 top-3 text-gray-300 text-sm"></i>
    </div>
  </div>

</div>

{{-- Program Studi --}}
<div>
  <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
    Program Studi
  </label>

  <div class="relative">
    <input
      type="text"
      name="dosen[{{ $i }}][program_studi]"
      value="{{ $prodi }}"
      placeholder="Contoh: Teknik Informatika"
      maxlength="100"
      list="prodiList"
      class="w-full border border-gray-200 rounded-xl px-3 py-2.5 pl-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

      <i class="fa-solid fa-building-columns absolute left-3 top-3 text-gray-300 text-sm"></i>
  </div>
</div>

<datalist id="prodiList">
  <option value="Teknik Informatika">
  <option value="Sistem Informasi">
  <option value="Teknik Elektro">
  <option value="Teknik Sipil">
  <option value="Teknik Mesin">
  <option value="Matematika">
  <option value="Fisika">
  <option value="Kimia">
  <option value="Manajemen">
  <option value="Akuntansi">
  <option value="Ilmu Hukum">
  <option value="Kedokteran">
</datalist>