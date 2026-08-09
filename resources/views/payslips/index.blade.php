<x-admin-layout>
    <div class="p-6 space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Slip Gaji</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Unggah dan kelola slip gaji (PDF) per pegawai.</p>
            </div>
        </section>

        <!-- FILTERS & CONTROLS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('payslips.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Box -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="relative w-full search-container">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari pegawai..."
                            style="padding-left: 0.75rem; padding-right: 2.25rem;"
                            class="w-full h-9 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 focus:border-slate-400 dark:focus:border-slate-600 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                        <button type="submit" 
                            :class="searchVal.trim() !== '' ? 'text-indigo-600 dark:text-indigo-400 scale-105' : 'text-slate-400 dark:text-slate-500'"
                            class="absolute right-0 top-0 h-full w-9 flex items-center justify-center hover:text-indigo-750 dark:hover:text-indigo-300 transition-all duration-200 cursor-pointer bg-transparent border-0">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Bulan -->
                    <input type="month" name="month" value="{{ request('month', $month) }}" onchange="this.form.submit()"
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer shadow-inner">

                    <!-- Filter Unit -->
                    <select name="unit_id" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <!-- Jabatan -->
                    <select name="position" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>

                    @if(request()->anyFilled(['search', 'unit_id', 'position']) || request()->filled('month') && request('month') != now()->format('Y-m') || request()->filled('per_page') && request('per_page') != 50)
                        <a href="{{ route('payslips.index') }}" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Right Side: Per Page Options -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-24 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </section>



        @php
            if (!function_exists('getInitials')) {
                function getInitials($name) {
                    if (empty($name)) return '?';
                    $words = explode(' ', $name);
                    if (count($words) >= 2) {
                        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    }
                    return strtoupper(substr($name, 0, 2));
                }
            }
            $colors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#14b8a6', '#f43f5e', '#0ea5e9', '#d946ef'];
        @endphp

        <!-- MAIN TABLE -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            <div class="overflow-x-auto" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 uppercase font-semibold border-b border-slate-200 dark:border-slate-800 sticky top-0 z-20">
                        <tr>
                            <th class="px-6 py-4 min-w-[200px]">Profil Pegawai</th>
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4 text-center">Status File</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($paginatedEmployees as $index => $emp)
                            @php
                                $empName = $emp['name'] ?? 'Tidak Diketahui';
                                $color = $colors[$index % count($colors)];
                                $initial = getInitials($empName);
                            @endphp
                            <tr class="group hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <!-- Photo/Avatar -->
                                        <div class="shrink-0">
                                            @if(!empty($emp['photo']))
                                                <img src="{{ str_contains($emp['photo'], 'photos/') ? rtrim($emp['unit_url'], '/') . '/storage/' . $emp['photo'] : rtrim($emp['unit_url'], '/') . '/storage/photos/' . $emp['photo'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200/50 dark:border-slate-800/40">
                                            @else
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm" style="background:{{ $color }}">{{ $initial }}</div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col min-w-0 text-left">
                                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ $empName }}</span>
                                            <div class="flex flex-col gap-0.5 mt-0.5">
                                                <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300 truncate max-w-[120px] inline-block w-max">{{ $emp['unit_name'] ?? '-' }}</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate" title="{{ $emp['position'] ?? '-' }}">{{ $emp['position'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-605 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if($emp['payslip'])
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                                                   <div class="flex gap-2 justify-end">
                                        @if($emp['payslip'])
                                            <a href="{{ Storage::url($emp['payslip']->file_path) }}" target="_blank"
                                               class="h-8 px-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1" title="Lihat Slip">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                Lihat
                                            </a>
                                            <form method="POST" action="{{ route('payslips.destroy', $emp['payslip']->id) }}" onsubmit="return confirm('Hapus slip gaji ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="openUploadModal('{{ $emp['id'] }}', '{{ $emp['unit_id'] }}', '{{ addslashes($emp['name']) }}')"
                                                    class="h-8 px-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-900/40 text-indigo-750 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200/30 dark:border-indigo-900/30 transition-all cursor-pointer flex items-center gap-1">
                                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                                Upload
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                        <i data-lucide="file-search" class="w-12 h-12 mb-4 text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-sm font-medium">Tidak ada data pegawai yang ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($paginatedEmployees instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginatedEmployees->total() > 0)
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedEmployees->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedEmployees->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedEmployees->total() }}</span>
                        pegawai
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if ($paginatedEmployees->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-650 flex items-center justify-center cursor-not-allowed select-none">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedEmployees->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-semibold text-slate-600 dark:text-slate-400">
                            Halaman {{ $paginatedEmployees->currentPage() }} dari {{ $paginatedEmployees->lastPage() }}
                        </span>

                        @if ($paginatedEmployees->hasMorePages())
                            <a href="{{ $paginatedEmployees->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-650 flex items-center justify-center cursor-not-allowed select-none">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-950/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300" id="uploadModalContent">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Upload Slip Gaji</h3>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-350 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="uploadForm" method="POST" action="{{ route('payslips.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="employee_id" id="modal_employee_id">
                <input type="hidden" name="school_unit_id" id="modal_unit_id">
                <input type="hidden" name="period" value="{{ $month }}">

                <div class="mb-4 text-left">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Pegawai</label>
                    <div id="modal_employee_name" class="p-3 bg-slate-50/50 dark:bg-slate-900/50 rounded-lg text-slate-800 dark:text-slate-200 font-semibold border border-slate-150 dark:border-slate-800 text-xs"></div>
                </div>

                <div class="mb-6 text-left">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">File PDF <span class="text-rose-500">*</span></label>
                    <input type="file" name="payslip_file" accept=".pdf" required
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-950/20 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/30 border border-slate-200 dark:border-slate-800 rounded-lg cursor-pointer bg-white dark:bg-slate-900">
                    <p class="mt-2 text-[10px] text-slate-400">Hanya file PDF (Maks. 1MB)</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-semibold text-xs cursor-pointer">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-lg hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors font-semibold text-xs cursor-pointer shadow-sm">Upload File</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(empId, unitId, empName) {
            document.getElementById('modal_employee_id').value = empId;
            document.getElementById('modal_unit_id').value = unitId;
            document.getElementById('modal_employee_name').innerText = empName;

            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            modal.classList.remove('hidden');
            void modal.offsetWidth;
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }

        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('uploadForm').reset();
            }, 300);
        }
    </script>
</x-admin-layout>

<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 280px !important;
        }
    }
</style>
