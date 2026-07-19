<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ showEmpDetailModal: false, selectedEmp: null }">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- ERROR ALERT -->
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Terjadi Kesalahan!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <!-- IMPORT ERRORS ALERT -->
        @if(session('import_errors'))
            <div class="bg-rose-50 dark:bg-rose-955/20 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-400 p-4 rounded-xl flex items-start gap-3 text-left w-full">
                <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0 text-rose-550 dark:text-rose-400"></i>
                <div class="space-y-1">
                    <h5 class="text-xs font-bold">Beberapa baris data gagal diimpor:</h5>
                    <ul class="list-disc list-inside text-[11px] leading-relaxed opacity-90 max-h-40 overflow-y-auto">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Pegawai Terintegrasi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar gabungan dan manajemen guru/staf dari seluruh unit sekolah yang terhubung.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="toggleModal('import-employee-modal')" class="h-9 px-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-855 text-slate-700 dark:text-slate-355 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-150 cursor-pointer flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-slate-500"></i>
                    Impor Pegawai
                </button>
                <a href="{{ route('employees.create') }}" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Tambah Pegawai
                </a>
            </div>
        </header>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <!-- Search Box -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama, email, NIP, atau jabatan..."
                        style="padding-left: 2.25rem;"
                        class="w-full h-9 pr-4 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 focus:border-slate-400 dark:focus:border-slate-600 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <!-- Unit -->
                    <select name="unit" onchange="this.form.submit()"
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($schoolUnits as $su)
                            <option value="{{ $su->id }}" {{ request('unit') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                        @endforeach
                    </select>

                    <!-- Status -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Aktif</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>

                    @if(request()->anyFilled(['search', 'unit', 'status']))
                        <a href="{{ route('employees.index') }}" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- DATA TABLE -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Daftar Pegawai Gabungan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Tipe Pegawai</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">NUPTK/NIP/NIK</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Unit Sekolah</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($employees as $index => $emp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($emp['photo']))
                                            <img src="{{ str_contains($emp['photo'], 'photos/') ? rtrim($emp['unit_url'], '/') . '/storage/' . $emp['photo'] : rtrim($emp['unit_url'], '/') . '/storage/photos/' . $emp['photo'] }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-350 shrink-0">
                                                {{ strtoupper(substr($emp['name'], 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div @click="selectedEmp = {
                                                name: '{{ $emp['name'] }}',
                                                nuptk_nip_nik: '{{ $emp['nuptk_nip_nik'] ?? '-' }}',
                                                subject_position: '{{ $emp['subject_position'] ?? '-' }}',
                                                unit: '{{ strtoupper($emp['unit_name'] ?? '-') }}',
                                                email: '{{ $emp['email'] ?? '-' }}',
                                                gender: '{{ $emp['gender'] ?? '-' }}',
                                                employment_status: '{{ $emp['employment_status'] ?? '-' }}',
                                                photo_url: '{{ !empty($emp['photo']) ? rtrim($emp['unit_url'], '/') . '/storage/' . (str_contains($emp['photo'], 'photos/') ? $emp['photo'] : 'photos/' . $emp['photo']) : '' }}'
                                            }; showEmpDetailModal = true" class="font-semibold text-slate-900 dark:text-slate-50 cursor-pointer hover:underline hover:text-indigo-650 dark:hover:text-indigo-400">{{ $emp['name'] }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp['subject_position'] }} • {{ $emp['gender'] == 'Male' ? 'Laki-laki' : 'Perempuan' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ $emp['employee_type']['name'] ?? 'Pegawai' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $emp['nuptk_nip_nik'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        {{ $emp['unit_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if(($emp['status'] ?? '') == 'Active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-800/40 uppercase">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/40 dark:border-rose-800/40 uppercase">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('employees.edit', [$emp['unit_id'], $emp['id']]) }}" class="h-8 px-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('employees.destroy', [$emp['unit_id'], $emp['id']]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="users-2" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Tidak ada data pegawai yang dapat ditampilkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION & COUNT FOOTER -->
            @if($employees instanceof \Illuminate\Pagination\LengthAwarePaginator && $employees->total() > 0)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-900 bg-slate-50/30 dark:bg-slate-900/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-350">{{ $employees->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-350">{{ $employees->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-350">{{ $employees->total() }}</span>
                        pegawai
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold">
                        @if ($employees->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20">Sebelumnya</span>
                        @else
                            <a href="{{ $employees->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-950">Sebelumnya</a>
                        @endif

                        <span class="px-3 py-1 font-medium text-slate-700 dark:text-slate-300">
                            Halaman {{ $employees->currentPage() }} dari {{ $employees->lastPage() }}
                        </span>

                        @if ($employees->hasMorePages())
                            <a href="{{ $employees->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-950">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        <!-- IMPORT MODAL -->
        <div id="import-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs hidden transition-opacity">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0 duration-200">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Impor Pegawai dari Excel</h3>
                    <button onclick="toggleModal('import-employee-modal')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-left text-xs">
                    @csrf
                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 p-4 rounded-lg border border-slate-200 dark:border-slate-800">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Format Template Pengisian</h4>
                        <p class="text-[11px] text-slate-550 dark:text-slate-400 leading-relaxed">
                            Unduh template Excel resmi terlebih dahulu untuk memahami susunan kolom data pegawai yang benar. Pastikan kolom "Unit Sekolah" diisi dengan tepat (paud/sd/smp).
                        </p>
                        <a href="{{ route('employees.download-template') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Unduh Template Excel (.xlsx)
                        </a>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-650 dark:text-slate-400 mb-1.5">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" accept=".xlsx, .xls" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-50 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-300 dark:hover:file:bg-slate-700 cursor-pointer">
                    </div>
                    <div class="p-5 border-t border-slate-200 dark:border-slate-850 flex justify-end gap-2.5">
                        <button type="button" onclick="toggleModal('import-employee-modal')" class="px-4 py-2 border border-slate-200 dark:border-slate-850 text-slate-700 dark:text-slate-355 bg-transparent text-xs font-bold rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-900 text-xs font-bold rounded-lg cursor-pointer">Mulai Impor</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function toggleModal(modalId) {
                const modal = document.getElementById(modalId);
                const content = modal.firstElementChild;
                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.style.opacity = '1';
                        content.style.opacity = '1';
                        content.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    content.style.opacity = '0';
                    content.style.transform = 'scale(0.95)';
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 200);
                }
            }
        </script>
        <!-- MODAL DETAIL PEGAWAI -->
        <div x-show="showEmpDetailModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-xs text-left" style="display: none;">
            <div @click.outside="showEmpDetailModal = false" class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-150 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-55 font-nasalization flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-indigo-650 dark:text-indigo-400"></i>
                        Profil Pegawai
                    </h3>
                    <button @click="showEmpDetailModal = false" class="text-slate-455 hover:text-slate-700 dark:hover:text-slate-355">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="p-5 space-y-6">
                    <div class="flex items-center gap-4">
                        <!-- Photo / Initials -->
                        <div class="shrink-0">
                            <template x-if="selectedEmp && selectedEmp.photo_url">
                                <img :src="selectedEmp.photo_url" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-800 shadow-sm">
                            </template>
                            <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                <div class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-955/40 text-indigo-650 dark:text-indigo-400 font-bold flex items-center justify-center text-2xl uppercase shadow-sm">
                                    <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                            <p class="text-slate-450 dark:text-slate-500 font-mono" x-text="selectedEmp ? 'NIP/NUPTK: ' + (selectedEmp.nuptk_nip_nik || '-') : ''"></p>
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-455 border border-indigo-200 dark:border-indigo-800 uppercase" x-text="selectedEmp ? selectedEmp.subject_position : ''"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-[11px] pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Unit Kerja</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200 uppercase" x-text="selectedEmp ? selectedEmp.unit : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Email</span>
                            <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.email : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jenis Kelamin</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : 'Perempuan') : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Status Pegawai</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.employment_status : ''"></span>
                        </div>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-150 dark:border-slate-850 flex justify-end">
                    <button @click="showEmpDetailModal = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-850 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
