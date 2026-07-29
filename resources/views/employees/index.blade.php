<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ showEmpDetailModal: false, selectedEmp: null }">

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
                <button onclick="toggleModal('create-employee-modal')" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Tambah Pegawai
                </button>
            </div>
        </header>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <!-- Search Box -->
                <div class="relative w-full md:max-w-2xl">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama, email, NIP, atau jabatan..."
                        style="padding-left: 2.25rem;"
                        class="w-full h-9 pr-4 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 focus:border-slate-400 dark:focus:border-slate-600 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Per Page -->
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-24 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>

                    <!-- Unit -->
                    <select name="unit" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($schoolUnits as $su)
                            <option value="{{ $su->id }}" {{ request('unit') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                        @endforeach
                    </select>

                    <!-- Status -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Status</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Aktif</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>

                    @if(request()->anyFilled(['search', 'unit', 'status']) || request()->filled('per_page') && request('per_page') != 50)
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
            <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 240px);">
                <table class="w-full text-xs border-collapse">
                                        <thead class="sticky top-0 z-10 shadow-sm">
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-14">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider min-w-[200px]">Nama Lengkap</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-40">Unit</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-40">Tipe Pegawai</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-56">Jabatan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-40">ZK ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($employees as $index => $emp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors text-left">
                                <td class="px-6 py-4 text-slate-900 dark:text-slate-50 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($emp['photo']))
                                            <img @click='selectedEmp = @json($emp); toggleModal("detail-employee-modal")' src="{{ str_contains($emp['photo'], 'photos/') ? rtrim($emp['unit_url'], '/') . '/storage/' . $emp['photo'] : rtrim($emp['unit_url'], '/') . '/storage/photos/' . $emp['photo'] }}" class="w-8 h-8 cursor-pointer rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div @click='selectedEmp = @json($emp); toggleModal("detail-employee-modal")' class="w-8 h-8 cursor-pointer rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-350 shrink-0">
                                                {{ strtoupper(substr($emp['name'], 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col text-left">
                                            @php
                                                $emp['photo_url'] = !empty($emp['photo']) ? rtrim($emp['unit_url'], '/') . '/storage/' . (str_contains($emp['photo'], 'photos/') ? $emp['photo'] : 'photos/' . $emp['photo']) : '';
                                                $emp['nik_nuptk'] = $emp['nik'] ?? $emp['nuptk'] ?? '-';
                                                $emp['unit_name'] = strtoupper($emp['unit_name'] ?? '-');
                                                $emp['zkteco_device_ids'] = !empty($emp['zkteco_uid']) ? \App\Models\EmployeeDeviceMapping::where('zkteco_uid', $emp['zkteco_uid'])->pluck('zkteco_device_id')->toArray() : [];
                                            @endphp
                                            <div @click='selectedEmp = @json($emp); toggleModal("detail-employee-modal")' class="font-semibold text-slate-900 dark:text-slate-50 cursor-pointer hover:text-slate-700 dark:hover:text-slate-300">{{ $emp['name'] }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp['email'] ?? 'Tidak ada email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if(str_contains(strtolower($emp['unit_name']), 'paud'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200/50 dark:border-teal-800/40 uppercase">PAUD & TK</span>
                                    @elseif(str_contains(strtolower($emp['unit_name']), 'sd'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200/50 dark:border-blue-800/40 uppercase">SD</span>
                                    @elseif(str_contains(strtolower($emp['unit_name']), 'smp'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-200/50 dark:border-purple-800/40 uppercase">SMP</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 uppercase tracking-wider">{{ $emp['unit_name'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ $emp['employee_type']['name'] ?? 'Pegawai' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-slate-700 dark:text-slate-300 font-medium">{{ $emp['position'] ?? '-' }}</span>
                                    @if(!empty($emp['additional_position']))
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $emp['additional_position'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if(!empty($emp['zkteco_uid']))
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono text-xs">ID: {{ $emp['zkteco_uid'] }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if(($emp['status'] ?? '') == 'Active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-lime-50 dark:bg-lime-950/30 text-lime-700 dark:text-lime-400 border border-lime-200/50 dark:border-lime-800/40">Aktif</span>
                                    @elseif(($emp['status'] ?? '') == 'Leave')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40">Cuti</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 dark:bg-red-955/20 text-red-700 dark:text-red-400 border border-red-200/50 dark:border-red-800/40">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button type="button" @click='selectedEmp = @json($emp); toggleModal("edit-employee-modal"); if(window.loadEditEmployeeTypes) window.loadEditEmployeeTypes(selectedEmp.unit_id, selectedEmp.employee_type_code || (selectedEmp.employee_type ? selectedEmp.employee_type.code : ""));' class="h-8 px-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </button>
                                        <button type="button" @click='selectedEmp = @json($emp); toggleModal("delete-employee-modal")' class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
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
        <div id="import-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm hidden transition-opacity">
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
        <div id="detail-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm hidden transition-opacity text-left">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-xl w-full overflow-hidden transform transition-all scale-95 opacity-0 duration-200 text-xs">
                
                <!-- Header -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Profil Pegawai</h3>
                    <button type="button" onclick="toggleModal('detail-employee-modal')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-6">
                    <div class="flex items-center gap-4">
                        <!-- Photo / Initials -->
                        <div class="shrink-0">
                            <template x-if="selectedEmp && selectedEmp.photo_url">
                                <img :src="selectedEmp.photo_url" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-800 shadow-sm">
                            </template>
                            <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold flex items-center justify-center text-2xl uppercase border border-slate-200 dark:border-slate-800 shadow-sm">
                                    <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                            <p class="text-slate-500 dark:text-slate-400 font-mono text-[11px]" x-text="selectedEmp ? 'NIP/NUPTK: ' + (selectedEmp.nik_nuptk || '-') : ''"></p>
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 uppercase" x-text="selectedEmp ? selectedEmp.position : ''"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-3 text-[11px] pt-4 border-canvas-soft dark:border-ink-deep max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                        <div class="col-span-full bg-negative/10 dark:bg-negative-bg/40 p-4 rounded-xl border border-negative/30 dark:border-negative-deep/40 space-y-3">
                            <h5 class="text-xs font-bold text-negative-deep dark:text-negative border-b border-negative/30 dark:border-negative-deep/40 pb-1.5">Informasi Umum</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">Unit Kerja</span>
                                    <span class="font-bold text-ink dark:text-canvas uppercase" x-text="selectedEmp ? selectedEmp.unit_name : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">Email</span>
                                    <span class="font-bold text-ink dark:text-canvas" x-text="selectedEmp ? (selectedEmp.email || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">Jenis Kelamin</span>
                                    <span class="font-bold text-ink dark:text-canvas" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : (selectedEmp.gender === 'Female' ? 'Perempuan' : '-')) : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">Tempat, Tgl Lahir</span>
                                    <span class="font-bold text-ink dark:text-canvas" x-text="selectedEmp ? ((selectedEmp.birth_place || '-') + ', ' + (selectedEmp.birth_date || '-')) : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">Alamat</span>
                                    <span class="font-bold text-ink dark:text-canvas" x-text="selectedEmp ? (selectedEmp.address || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-negative-deep/60 dark:text-negative/60 text-[9px] uppercase font-semibold">No. HP</span>
                                    <span class="font-bold text-ink dark:text-canvas" x-text="selectedEmp ? (selectedEmp.phone || '-') : '-'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full bg-warning/10 dark:bg-warning-deep/20 p-4 rounded-xl border border-warning/40 dark:border-warning-deep/40 space-y-3">
                            <h5 class="text-xs font-bold text-warning-content dark:text-warning border-b border-warning/40 dark:border-warning-deep/40 pb-1.5">Informasi Status Pegawai</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">Status Pegawai</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.employment_status || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">Jabatan</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.position || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">Tugas Tambahan</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.additional_position || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">Masa Kerja</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.work_period || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">Pangkat/Golongan</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.pangkat_golongan || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-warning-content/60 dark:text-warning/60 text-[9px] uppercase font-semibold">ID ZKTeco</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.zkteco_uid || '-') : '-'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full bg-primary/10 dark:bg-ink-deep/40 p-4 rounded-xl border border-primary/30 dark:border-primary-neutral/20 space-y-3">
                            <h5 class="text-xs font-bold text-ink-deep dark:text-primary-neutral border-b border-primary/30 dark:border-primary-neutral/20 pb-1.5">Informasi Pegawai</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">NIK</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.nik || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">NUPTK</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.nuptk || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">NIY</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.niy || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">No. UKG</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.no_ukg || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">NRG</span>
                                    <span class="font-bold text-body dark:text-canvas-soft font-mono" x-text="selectedEmp ? (selectedEmp.nrg || '-') : '-'"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-span-full bg-positive/10 dark:bg-ink-deep/40 p-4 rounded-xl border border-positive/30 dark:border-positive-deep/20 space-y-3">
                            <h5 class="text-xs font-bold text-ink-deep dark:text-positive-deep border-b border-positive/30 dark:border-positive-deep/20 pb-1.5">Pendidikan & SK</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">Pendidikan Terakhir</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.last_education || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">Jurusan</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.major || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">Tgl Mulai Tugas</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? (selectedEmp.task_start_date || '-') : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-mute text-[9px] uppercase font-semibold">Info SK</span>
                                    <span class="font-bold text-body dark:text-canvas-soft" x-text="selectedEmp ? ((selectedEmp.last_sk_number || 'Tidak Ada SK') + (selectedEmp.last_sk_date ? ' (' + selectedEmp.last_sk_date + ')' : '')) : '-'"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-span-full mt-2">
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Catatan</span>
                            <div class="p-2 bg-slate-50 dark:bg-slate-900 rounded border border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400 mt-1" x-text="selectedEmp && selectedEmp.notes ? selectedEmp.notes : 'Tidak ada catatan tambahan.'"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-5 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="button" onclick="toggleModal('detail-employee-modal')" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-transparent text-xs font-bold rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">Tutup</button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL TAMBAH PEGAWAI (SLIDE PANEL) ===== -->
        <div id="create-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm hidden transition-opacity text-left">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-xl w-full max-h-[90vh] flex flex-col overflow-hidden transform transition-all scale-95 opacity-0 duration-200 text-xs">

                <!-- Header -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tambah Pegawai Baru</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Tambahkan guru atau staf baru ke dalam unit sekolah terkait.</p>
                    </div>
                    <button type="button" onclick="toggleModal('create-employee-modal')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Scrollable Form Body -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <form id="create-employee-form" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Unit Sekolah -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Unit Sekolah Tujuan <span class="text-rose-500">*</span></label>
                                <select name="school_unit_id" id="modal_school_unit_id" required class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="">-- Pilih Unit Sekolah --</option>
                                    @foreach($schoolUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Drs. Eko Wibowo, M.Pd"
                                    class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" required placeholder="Contoh: nama@sans.dev"
                                    class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- Tipe Pegawai -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe Pegawai <span class="text-rose-500">*</span></label>
                                <select name="employee_type_code" id="modal_employee_type_code" required disabled class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="">-- Pilih Unit Sekolah Dahulu --</option>
                                </select>
                            </div>

                            <!-- SECTION: DATA DIRI -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Diri</h4>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                <input type="text" name="birth_place" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                <input type="date" name="birth_date" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                <select name="gender" required class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="Male">Laki-laki</option>
                                    <option value="Female">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat</label>
                                <input type="text" name="address" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">No. HP / WA</label>
                                <input type="text" name="phone" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- SECTION: DATA KEPEGAWAIAN -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Kepegawaian &amp; Identitas</h4>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NIK</label>
                                <input type="text" name="nik" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NIY</label>
                                <input type="text" name="niy" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NUPTK</label>
                                <input type="text" name="nuptk" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NO UKG</label>
                                <input type="text" name="no_ukg" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NRG</label>
                                <input type="text" name="nrg" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                                <input type="text" name="last_education" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jurusan</label>
                                <input type="text" name="major" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Utama</label>
                                <input type="text" name="position" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Tambahan</label>
                                <input type="text" name="additional_position" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                                <input type="text" name="employment_status" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Mulai Tugas</label>
                                <input type="date" name="task_start_date" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Diangkat</label>
                                <input type="date" name="appointment_date" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal SK Terakhir</label>
                                <input type="date" name="last_sk_date" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Nomor SK Terakhir</label>
                                <input type="text" name="last_sk_number" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Masa Kerja Golongan</label>
                                <input type="text" name="work_period" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- SECTION: SISTEM ABSENSI -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Sistem Absensi &amp; Foto</h4>
                            </div>

                            @if(auth()->user()->role === 'super_admin')
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">ID ZKTeco / PIN Fingerprint</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="zkteco_uid" id="modal_zkteco_uid" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                                    <button type="button" id="modal_btn_generate_uid" class="px-3 h-9 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">Buat ID Otomatis</button>
                                </div>
                            </div>
                            <div class="col-span-full">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-2">Sinkronisasi Mesin ZKTeco (Opsional)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($devices as $dev)
                                    <label class="flex items-center gap-2 p-2 border rounded-lg dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                                        <input type="checkbox" name="zkteco_device_ids[]" value="{{ $dev->id }}" class="rounded border-slate-300 text-blue-600">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $dev->name }} <span class="text-slate-400">({{ $dev->sn }})</span></span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan <span class="text-rose-500">*</span></label>
                                <select name="status" required class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="Active" selected>Aktif</option>
                                    <option value="Leave">Cuti</option>
                                    <option value="Inactive">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-span-full">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Foto Profil</label>
                                <input type="file" name="photo" accept="image/*"
                                    class="w-full text-sm px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-50 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-300 dark:hover:file:bg-slate-700 cursor-pointer">
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Footer Actions -->
                <div class="flex-none p-5 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" onclick="toggleModal('create-employee-modal')" class="px-4 py-2 border border-slate-200 dark:border-slate-850 text-slate-700 dark:text-slate-355 bg-transparent text-xs font-bold rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">Batal</button>
                    <button type="submit" form="create-employee-form" class="px-4 py-2 bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-900 text-xs font-bold rounded-lg cursor-pointer hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors">Simpan Data Pegawai</button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL EDIT PEGAWAI ===== -->
        <div id="edit-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm hidden transition-opacity text-left">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-xl w-full max-h-[90vh] flex flex-col overflow-hidden transform transition-all scale-95 opacity-0 duration-200 text-xs">

                <!-- Header -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Edit Data Pegawai</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbarui data guru atau staf pada unit sekolah secara terpusat.</p>
                    </div>
                    <button type="button" onclick="toggleModal('edit-employee-modal')" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Scrollable Form Body -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <form id="edit-employee-form" method="POST" :action="selectedEmp ? '/employees/' + selectedEmp.unit_id + '/' + selectedEmp.id : ''" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required :value="selectedEmp ? selectedEmp.name : ''"
                                    class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" required :value="selectedEmp ? selectedEmp.email : ''"
                                    class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- Tipe Pegawai -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe Pegawai <span class="text-rose-500">*</span></label>
                                <select name="employee_type_code" id="modal_edit_employee_type_code" required class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="">Memuat...</option>
                                </select>
                            </div>

                            <!-- SECTION: DATA DIRI -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Diri</h4>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                <input type="text" name="birth_place" :value="selectedEmp ? selectedEmp.birth_place : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                <input type="date" name="birth_date" :value="selectedEmp ? selectedEmp.birth_date : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                <select name="gender" required :value="selectedEmp ? selectedEmp.gender : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="Male">Laki-laki</option>
                                    <option value="Female">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat</label>
                                <input type="text" name="address" :value="selectedEmp ? selectedEmp.address : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">No. HP / WA</label>
                                <input type="text" name="phone" :value="selectedEmp ? selectedEmp.phone : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- SECTION: DATA KEPEGAWAIAN -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Kepegawaian &amp; Identitas</h4>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NIK</label>
                                <input type="text" name="nik" :value="selectedEmp ? selectedEmp.nik : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NIY</label>
                                <input type="text" name="niy" :value="selectedEmp ? selectedEmp.niy : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NUPTK</label>
                                <input type="text" name="nuptk" :value="selectedEmp ? selectedEmp.nuptk : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NO UKG</label>
                                <input type="text" name="no_ukg" :value="selectedEmp ? selectedEmp.no_ukg : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">NRG</label>
                                <input type="text" name="nrg" :value="selectedEmp ? selectedEmp.nrg : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan" :value="selectedEmp ? selectedEmp.pangkat_golongan : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                                <input type="text" name="last_education" :value="selectedEmp ? selectedEmp.last_education : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jurusan</label>
                                <input type="text" name="major" :value="selectedEmp ? selectedEmp.major : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Utama</label>
                                <input type="text" name="position" :value="selectedEmp ? selectedEmp.position : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Tambahan</label>
                                <input type="text" name="additional_position" :value="selectedEmp ? selectedEmp.additional_position : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                                <input type="text" name="employment_status" :value="selectedEmp ? selectedEmp.employment_status : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Mulai Tugas</label>
                                <input type="date" name="task_start_date" :value="selectedEmp ? selectedEmp.task_start_date : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Diangkat</label>
                                <input type="date" name="appointment_date" :value="selectedEmp ? selectedEmp.appointment_date : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal SK Terakhir</label>
                                <input type="date" name="last_sk_date" :value="selectedEmp ? selectedEmp.last_sk_date : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Nomor SK Terakhir</label>
                                <input type="text" name="last_sk_number" :value="selectedEmp ? selectedEmp.last_sk_number : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Masa Kerja Golongan</label>
                                <input type="text" name="work_period" :value="selectedEmp ? selectedEmp.work_period : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            </div>

                            <!-- SECTION: SISTEM ABSENSI -->
                            <div class="col-span-full mt-2 mb-1 border-b border-slate-200 dark:border-slate-800 pb-1.5">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Sistem Absensi &amp; Foto</h4>
                            </div>

                            @if(auth()->user()->role === 'super_admin')
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">ID ZKTeco / PIN Fingerprint</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="zkteco_uid" id="modal_edit_zkteco_uid" :value="selectedEmp ? selectedEmp.zkteco_uid : ''" class="w-full text-sm h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                                    <button type="button" id="modal_edit_btn_generate_uid" class="px-3 h-9 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">Buat ID Otomatis</button>
                                </div>
                            </div>
                            <div class="col-span-full">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-2">Sinkronisasi Mesin ZKTeco (Opsional)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($devices as $dev)
                                    <label class="flex items-center gap-2 p-2 border rounded-lg dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                                        <input type="checkbox" name="zkteco_device_ids[]" value="{{ $dev->id }}" x-bind:checked="selectedEmp && selectedEmp.zkteco_device_ids && selectedEmp.zkteco_device_ids.includes({{ $dev->id }})" class="rounded border-slate-300 text-blue-600">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $dev->name }} <span class="text-slate-400">({{ $dev->sn }})</span></span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan <span class="text-rose-500">*</span></label>
                                <select name="status" required :value="selectedEmp ? selectedEmp.status : ''" class="w-full text-sm h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                    <option value="Active">Aktif</option>
                                    <option value="Leave">Cuti</option>
                                    <option value="Inactive">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-span-full">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-1">Foto Profil</label>
                                <input type="file" name="photo" accept="image/*"
                                    class="w-full text-sm px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-50 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-300 dark:hover:file:bg-slate-700 cursor-pointer">
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Footer Actions -->
                <div class="flex-none p-5 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" onclick="toggleModal('edit-employee-modal')" class="px-4 py-2 border border-slate-200 dark:border-slate-850 text-slate-700 dark:text-slate-355 bg-transparent text-xs font-bold rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">Batal</button>
                    <button type="submit" form="edit-employee-form" class="px-4 py-2 bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-900 text-xs font-bold rounded-lg cursor-pointer hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors">Simpan Perubahan</button>
                </div>
            </div>
        </div>
        <!-- ===== MODAL HAPUS PEGAWAI ===== -->
        <div id="delete-employee-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm hidden transition-opacity text-left">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-sm w-full overflow-hidden transform transition-all scale-95 opacity-0 duration-200 text-xs">
                
                <!-- Body -->
                <div class="p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50 mb-2">Hapus Pegawai?</h3>
                    <p class="text-[13px] text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                        Anda yakin ingin menghapus <strong class="text-slate-700 dark:text-slate-300" x-text="selectedEmp ? selectedEmp.name : ''"></strong> dari unit <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedEmp ? selectedEmp.unit_name : ''"></span>? Data yang telah dihapus tidak dapat dikembalikan.
                    </p>
                    
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="toggleModal('delete-employee-modal')" class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold rounded-xl cursor-pointer transition-colors shadow-sm">
                            Batal
                        </button>
                        <form id="delete-employee-form" method="POST" :action="selectedEmp ? '/employees/' + selectedEmp.unit_id + '/' + selectedEmp.id : ''" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl cursor-pointer transition-colors shadow-sm flex items-center justify-center gap-2">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script for modal create employee -->
        <script>
            (function() {
                const unitSel = document.getElementById('modal_school_unit_id');
                const typeSel = document.getElementById('modal_employee_type_code');

                function loadModalEmployeeTypes(unitId) {
                    if (!unitId) {
                        typeSel.innerHTML = '<option value="">-- Pilih Unit Sekolah Dahulu --</option>';
                        typeSel.disabled = true;
                        return;
                    }
                    typeSel.disabled = true;
                    typeSel.innerHTML = '<option value="">Memuat tipe pegawai...</option>';
                    fetch(`/school-units/${unitId}/employee-types`)
                        .then(r => r.json())
                        .then(data => {
                            typeSel.innerHTML = '';
                            if (!data.length) {
                                typeSel.innerHTML = '<option value="">Tidak ada tipe tersedia</option>';
                                return;
                            }
                            data.forEach(t => {
                                const o = document.createElement('option');
                                o.value = t.code;
                                o.textContent = `${t.name} (${t.code})`;
                                typeSel.appendChild(o);
                            });
                            typeSel.disabled = false;
                        })
                        .catch(() => {
                            typeSel.innerHTML = '<option value="">Gagal memuat data</option>';
                        });
                }

                if (unitSel) {
                    unitSel.addEventListener('change', e => loadModalEmployeeTypes(e.target.value));
                }

                // Generate UID Create
                const btnGen = document.getElementById('modal_btn_generate_uid');
                const inputUid = document.getElementById('modal_zkteco_uid');
                if (btnGen && inputUid) {
                    btnGen.addEventListener('click', function() {
                        const unitId = unitSel ? unitSel.value : '';
                        if (!unitId) { alert('Silakan pilih Unit Sekolah terlebih dahulu!'); return; }
                        const orig = btnGen.textContent;
                        btnGen.textContent = 'Memuat...';
                        btnGen.disabled = true;
                        fetch(`/employees/generate-uid/${unitId}`)
                            .then(r => r.json())
                            .then(d => { if (d.status === 'success') inputUid.value = d.next_uid; else alert('Gagal memuat UID'); })
                            .catch(() => alert('Terjadi kesalahan jaringan.'))
                            .finally(() => { btnGen.textContent = orig; btnGen.disabled = false; });
                    });
                }
                
                // Load Employee Types for Edit Modal
                window.loadEditEmployeeTypes = function(unitId, currentTypeCode) {
                    const typeSel = document.getElementById('modal_edit_employee_type_code');
                    if (!typeSel) return;
                    typeSel.disabled = true;
                    typeSel.innerHTML = '<option value="">Memuat...</option>';
                    fetch(`/school-units/${unitId}/employee-types`)
                        .then(r => r.json())
                        .then(data => {
                            typeSel.innerHTML = '';
                            if (!data.length) {
                                typeSel.innerHTML = '<option value="">Tidak ada tipe tersedia</option>';
                                return;
                            }
                            data.forEach(t => {
                                const o = document.createElement('option');
                                o.value = t.code;
                                o.textContent = `${t.name} (${t.code})`;
                                if (t.code === currentTypeCode) o.selected = true;
                                typeSel.appendChild(o);
                            });
                            typeSel.disabled = false;
                        });
                };
                
                // Generate UID Edit
                const btnGenEdit = document.getElementById('modal_edit_btn_generate_uid');
                const inputUidEdit = document.getElementById('modal_edit_zkteco_uid');
                if (btnGenEdit && inputUidEdit) {
                    btnGenEdit.addEventListener('click', function() {
                        // In alpine, selectedEmp is stored on the document context or we can get it from the window if bound, 
                        // but since we are outside Alpine component, we can use the form action to extract unit_id
                        const form = document.getElementById('edit-employee-form');
                        const action = form.getAttribute('action') || form.action;
                        // Expected action format: /employees/{unitId}/{id}
                        const match = action.match(/\/employees\/(\d+)\/\d+/);
                        const unitId = match ? match[1] : '';
                        
                        if (!unitId) { alert('Unit Sekolah tidak terdeteksi!'); return; }
                        const orig = btnGenEdit.textContent;
                        btnGenEdit.textContent = 'Memuat...';
                        btnGenEdit.disabled = true;
                        fetch(`/employees/generate-uid/${unitId}`)
                            .then(r => r.json())
                            .then(d => { if (d.status === 'success') inputUidEdit.value = d.next_uid; else alert('Gagal memuat UID'); })
                            .catch(() => alert('Terjadi kesalahan jaringan.'))
                            .finally(() => { btnGenEdit.textContent = orig; btnGenEdit.disabled = false; });
                    });
                }

                // Reset form when create modal is closed
                const _origToggle = window.toggleModal;
                window.toggleModal = function(modalId) {
                    if (modalId === 'create-employee-modal') {
                        const modal = document.getElementById(modalId);
                        const panel = modal.firstElementChild;
                        if (modal.classList.contains('hidden')) {
                            modal.classList.remove('hidden');
                            setTimeout(() => {
                                modal.style.opacity = '1';
                                panel.style.opacity = '1';
                                panel.style.transform = 'scale(1)';
                            }, 20);
                        } else {
                            panel.style.opacity = '0';
                            panel.style.transform = 'scale(0.95)';
                            modal.style.opacity = '0';
                            setTimeout(() => modal.classList.add('hidden'), 200);
                        }
                    } else {
                        _origToggle(modalId);
                    }
                };
            })();
        </script>
    </div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>
</x-admin-layout>
