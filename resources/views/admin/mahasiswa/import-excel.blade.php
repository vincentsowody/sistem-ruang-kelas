<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Data Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- FASE 1: Form Upload (Akan hilang jika data sedang di-preview) -->
            @if(!isset($dataDariExcel))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Langkah 1: Unggah File Excel</h3>
                    <p class="text-gray-600 mb-4">Pastikan file Excel Anda memiliki header pada baris pertama dengan urutan kolom: <strong>A: NIM, B: Nama, C: Email, D: Program Studi</strong>.</p>
                    
                    <form action="{{ route('mahasiswa-import.scan') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                        @csrf
                        <input type="file" name="file_excel" accept=".xlsx, .xls" required class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Scan File Excel
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- FASE 2: Hasil Scan & Form Simpan -->
            @if(isset($dataDariExcel))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Langkah 2: Konfirmasi Data</h3>
                    
                    <form action="{{ route('mahasiswa-import.simpan') }}" method="POST">
                        @csrf
                        
                        <!-- TABEL DATA BARU (Bisa Disimpan) -->
                        <h4 class="font-semibold text-green-600 mt-4 mb-2">Data Baru (Siap Di-import): {{ count($belumAda) }} Mahasiswa</h4>
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 border">Pilih</th>
                                        <th class="p-2 border">NIM</th>
                                        <th class="p-2 border">Nama</th>
                                        <th class="p-2 border">Email</th>
                                        <th class="p-2 border">Prodi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($belumAda as $idx => $mhs)
                                    <tr>
                                        <td class="p-2 border text-center">
                                            <input type="checkbox" name="mahasiswa[{{ $idx }}][pilih]" value="1" checked>
                                        </td>
                                        <td class="p-2 border">
                                            <input type="text" name="mahasiswa[{{ $idx }}][nim]" value="{{ $mhs['nim'] }}" class="w-full border-none bg-transparent" readonly>
                                        </td>
                                        <td class="p-2 border">
                                            <input type="text" name="mahasiswa[{{ $idx }}][nama]" value="{{ $mhs['nama'] }}" class="w-full border-none bg-transparent" readonly>
                                        </td>
                                        <td class="p-2 border">
                                            <input type="email" name="mahasiswa[{{ $idx }}][email]" value="{{ $mhs['email'] }}" class="w-full border-gray-300 rounded">
                                        </td>
                                        <td class="p-2 border">
                                            <input type="text" name="mahasiswa[{{ $idx }}][program_studi]" value="{{ $mhs['prodi'] }}" class="w-full border-gray-300 rounded">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-gray-500">Semua data di Excel sudah terdaftar di database.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- TABEL DATA DUPLIKAT (Hanya Info) -->
                        @if(count($sudahAda) > 0)
                        <h4 class="font-semibold text-red-600 mt-6 mb-2">Data Dilewati (Sudah Terdaftar): {{ count($sudahAda) }} Mahasiswa</h4>
                        <div class="overflow-x-auto mb-6 opacity-70">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-red-50">
                                        <th class="p-2 border">NIM</th>
                                        <th class="p-2 border">Nama di Excel</th>
                                        <th class="p-2 border">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sudahAda as $item)
                                    <tr>
                                        <td class="p-2 border">{{ $item['excel']['nim'] }}</td>
                                        <td class="p-2 border">{{ $item['excel']['nama'] }}</td>
                                        <td class="p-2 border text-red-500 text-sm">NIM atau Email sudah ada di sistem.</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <div class="flex items-center gap-4 mt-6">
                            <a href="{{ route('mahasiswa-import.form') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Batal / Upload Ulang</a>
                            
                            @if(count($belumAda) > 0)
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Data Terpilih
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>