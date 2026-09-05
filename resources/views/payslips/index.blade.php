<x-admin-layout>
    <div class="p-6 space-y-6">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2.5">
                    <span>Data Slip Gaji</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0 font-sans">Payslip</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Unggah dan kelola slip gaji (PDF) per pegawai.</p>
            </div>
        </header>

        <div id="payslip-report-container" class="space-y-6">

        <!-- FILTERS & CONTROLS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('payslips.index') }}" id="payslip-filter-form" data-no-loader="true" class="space-y-4">
                <input type="hidden" name="unit_id" id="filter-unit-id" value="{{ request('unit_id', $unitId) }}">

                <!-- Unit Pills Filter -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 border-b border-slate-150 dark:border-slate-800/60 w-full">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider shrink-0 mr-1.5 flex items-center gap-1">
                        <i data-lucide="school" class="w-3.5 h-3.5"></i>
                        Unit:
                    </span>
                    
                    <!-- Semua Unit Pill -->
                    <button type="button" 
                            onclick="selectUnitFilter('', this)"
                            class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ empty(request('unit_id', $unitId)) ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                        Semua Unit
                    </button>
                    
                    @foreach($units as $u)
                        <button type="button"
                                onclick="selectUnitFilter('{{ $u->id }}', this)"
                                class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ request('unit_id', $unitId) == $u->id ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                            {{ $u->name }}
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                    <!-- Left Side: Search & Filters -->
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <!-- Search Box -->
                        <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center flex-1 min-w-[200px] search-container bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                            <input type="text" name="search" x-model="searchVal" placeholder="Cari pegawai..."
                                style="border: none !important; outline: none !important; box-shadow: none !important;"
                                class="w-full h-10 px-3.5 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-550 focus:ring-0 focus:outline-none">
                            
                            <!-- Clear Button (x) -->
                            <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('.search-container').querySelector('input').focus();" class="h-10 px-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center" title="Bersihkan pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>

                            <button type="submit" 
                                :class="searchVal.trim() !== '' ? 'bg-indigo-650 text-white dark:bg-indigo-600' : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-350'"
                                class="h-10 px-4 font-bold text-xs transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center justify-center border-0">
                                Cari
                            </button>
                        </div>

                        <!-- Bulan (Manual Custom Month) -->
                        <div class="flex items-center gap-1.5">
                            <input type="month" name="month" id="filter-month-input" value="{{ request('month', $month) }}" onchange="triggerFilterForm(this)"
                                class="h-10 px-3 flex-1 sm:flex-initial sm:w-36 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 font-mono" title="Pilih periode bulan kustom">
                        </div>

                        <!-- Jabatan -->
                        <select name="position" onchange="triggerFilterForm(this)"
                            class="h-10 pl-3 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Jabatan</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>

                        @if(request()->anyFilled(['search', 'unit_id', 'position']) || request()->filled('month') && request('month') != ($lastMonth ?? \Carbon\Carbon::now()->subMonth()->format('Y-m')) || request()->filled('per_page') && request('per_page') != 50)
                            <a href="{{ route('payslips.index') }}" class="h-10 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-xl transition-colors reset-filter-btn" data-no-loader="true" title="Reset Filter">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Right Side: Per Page & Sync -->
                    <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                        <form action="{{ route('payslips.sync', ['month' => $month]) }}" method="POST" class="inline" data-no-loader="true" onsubmit="this.querySelector('button').style.pointerEvents = 'none'; let icon = this.querySelector('i, svg'); if(icon) icon.classList.add('animate-spin');">
                            @csrf
                            <button type="submit" class="h-10 px-4 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-650 dark:text-indigo-400 text-xs font-semibold rounded-xl border border-indigo-200 dark:border-indigo-800 transition-all cursor-pointer flex items-center gap-1.5 shadow-3xs hover:scale-105 duration-150 border-0" title="Sinkronisasi slip gaji bulan ini ke seluruh unit">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                Sync Gaji ke Unit
                            </button>
                        </form>
                        <select name="per_page" onchange="triggerFilterForm(this)"
                            class="h-10 pl-3 pr-8 flex-1 sm:flex-initial sm:w-24 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                            <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>
                </div>
            </form>
        </section>

        <!-- ACTIVE PERIOD BANNER -->
        <section class="bg-gradient-to-r from-indigo-50/90 via-slate-50/80 to-indigo-50/50 dark:from-indigo-950/40 dark:via-slate-900/60 dark:to-indigo-950/20 border border-indigo-200/70 dark:border-indigo-900/50 rounded-2xl p-4 shadow-2xs w-full flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-left">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="calendar-check-2" class="w-5 h-5"></i>
                </div>
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-indigo-100/90 dark:bg-indigo-900/60 px-2 py-0.5 rounded">Periode Slip Gaji Terpilih</span>
                        @if($month === ($lastMonth ?? \Carbon\Carbon::now()->subMonth()->format('Y-m')))
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/70 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-900/40 flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i> Periode Penggajian Terakhir
                            </span>
                        @elseif($month === ($currentMonth ?? \Carbon\Carbon::now()->format('Y-m')))
                            <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-950/60 px-2 py-0.5 rounded border border-amber-200/60 dark:border-amber-900/40 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Bulan Berjalan (Masa Kerja Belum Selesai)
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 bg-slate-200/70 dark:bg-slate-800 px-2 py-0.5 rounded">
                                Arsip Lampau
                            </span>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50 font-nasalization tracking-wide">
                        {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                    </h3>
                </div>
            </div>

            <div class="text-xs text-slate-600 dark:text-slate-400 bg-white/80 dark:bg-slate-900/80 px-3.5 py-2 rounded-xl border border-slate-200/60 dark:border-slate-800/80 shadow-2xs">
                <span class="text-slate-400 dark:text-slate-500">Keterangan:</span> Semua slip gaji yang diunggah akan tercatat untuk masa kerja <strong class="text-indigo-600 dark:text-indigo-400">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</strong>.
            </div>
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
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-bold text-slate-600 dark:text-slate-300 truncate max-w-[120px] inline-block w-max shrink-0">{{ $emp['unit_name'] ?? '-' }}</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-450 truncate" title="{{ $emp['position'] ?? '-' }}">{{ $emp['position'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-605 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                                </td>
                                 <td class="px-6 py-3 text-center">
                                     @if($emp['payslip'])
                                         <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-450 border border-emerald-100/30 dark:border-emerald-900/30">
                                             <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                             Tersedia
                                         </span>
                                     @else
                                         <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/20 text-rose-650 dark:text-rose-450 border border-rose-100/20 dark:border-rose-900/30">
                                             <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                             Kosong
                                         </span>
                                     @endif
                                 </td>
                                 <td class="px-6 py-3 text-right">
                                     <div class="flex gap-2 justify-end">
                                         @if($emp['payslip'])
                                             <a href="{{ Storage::url($emp['payslip']->file_path) }}" target="_blank"
                                                class="h-8 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-all hover:scale-105 duration-150 cursor-pointer flex items-center gap-1" title="Lihat Slip">
                                                 <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                 Lihat
                                             </a>
                                             @if($emp['payslip']->attachment_path)
                                                 <a href="{{ Storage::url($emp['payslip']->attachment_path) }}" target="_blank"
                                                    class="h-8 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-100/30 dark:border-indigo-900/30 transition-all hover:scale-105 duration-150 cursor-pointer flex items-center gap-1 border-0" title="Lihat Lampiran">
                                                     <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                                     Lampiran
                                                 </a>
                                             @endif
                                             <button type="button" onclick="openUploadModal(this)"
                                                     data-employee-id="{{ $emp['id'] }}"
                                                     data-unit-id="{{ $emp['unit_id'] }}"
                                                     data-employee-name="{{ $emp['name'] }}"
                                                     data-has-payslip="true"
                                                     data-payslip-url="{{ Storage::url($emp['payslip']->file_path) }}"
                                                     data-payslip-name="{{ basename($emp['payslip']->file_path) }}"
                                                     data-attachment-url="{{ $emp['payslip']->attachment_path ? Storage::url($emp['payslip']->attachment_path) : '' }}"
                                                     data-attachment-name="{{ $emp['payslip']->attachment_path ? basename($emp['payslip']->attachment_path) : '' }}"
                                                     class="h-8 px-3 bg-amber-50 hover:bg-amber-100 dark:bg-amber-955/20 dark:hover:bg-amber-955/40 text-amber-600 dark:text-amber-400 text-xs font-bold rounded-lg border border-amber-100/30 dark:border-amber-900/30 transition-all hover:scale-105 duration-150 cursor-pointer flex items-center gap-1 border-0" title="Edit Slip Gaji">
                                                 <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                 Edit
                                             </button>
                                             <form method="POST" action="{{ route('payslips.destroy', $emp['payslip']->id) }}" onsubmit="return confirm('Hapus slip gaji ini?');" class="inline">
                                                 @csrf
                                                 @method('DELETE')
                                                 <input type="hidden" name="redirect_url" value="{{ request()->fullUrl() }}">
                                                 <button type="submit"
                                                         class="h-8 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-955/30 dark:hover:bg-rose-955/50 text-rose-650 dark:text-rose-400 text-xs font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 cursor-pointer flex items-center gap-1 border-0" title="Hapus">
                                                     <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                     Hapus
                                                 </button>
                                             </form>
                                         @else
                                             <button type="button" onclick="openUploadModal(this)"
                                                     data-employee-id="{{ $emp['id'] }}"
                                                     data-unit-id="{{ $emp['unit_id'] }}"
                                                     data-employee-name="{{ $emp['name'] }}"
                                                     data-has-payslip="false"
                                                     class="h-8 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-100/30 dark:border-indigo-900/30 transition-all hover:scale-105 duration-150 cursor-pointer flex items-center gap-1 border-0">
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
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-405 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none font-bold">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedEmployees->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 font-bold hover:scale-105 duration-150">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-bold text-slate-600 dark:text-slate-400">
                            Halaman {{ $paginatedEmployees->currentPage() }} dari {{ $paginatedEmployees->lastPage() }}
                        </span>

                        @if ($paginatedEmployees->hasMorePages())
                            <a href="{{ $paginatedEmployees->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 font-bold hover:scale-105 duration-150">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-405 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none font-bold">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300" id="uploadModalContent">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3.5 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 uppercase tracking-wider">Upload Slip Gaji</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Periode: <strong id="modal_period_title" class="text-indigo-600 dark:text-indigo-400">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</strong></p>
                </div>
                <button onclick="closeUploadModal()" class="text-slate-450 hover:text-slate-655 transition-colors border-0 bg-transparent cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Prominent Target Period Alert Box -->
            <div class="p-3 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/70 dark:border-indigo-900/60 rounded-xl flex items-center justify-between gap-3 text-xs mb-3 shadow-3xs">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 bg-indigo-600 text-white rounded-lg shrink-0">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Target Periode Gaji</div>
                        <strong id="modal_period_badge" class="text-indigo-700 dark:text-indigo-300 font-bold text-xs">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</strong>
                    </div>
                </div>
                <span id="modal_period_code" class="text-[10px] bg-white dark:bg-slate-900 px-2.5 py-1 rounded-lg border border-indigo-200/60 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold font-mono">{{ $month }}</span>
            </div>

            <form id="uploadForm" method="POST" action="{{ route('payslips.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="employee_id" id="modal_employee_id">
                <input type="hidden" name="school_unit_id" id="modal_unit_id">
                <input type="hidden" name="period" value="{{ $month }}">
                <input type="hidden" name="redirect_url" id="modal_redirect_url" value="{{ request()->fullUrl() }}">

                <div class="text-left">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Pegawai</label>
                    <div id="modal_employee_name" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl text-slate-800 dark:text-slate-200 font-semibold border border-slate-200/50 dark:border-slate-800 text-xs"></div>
                </div>

                <div class="text-left">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">File PDF Slip Gaji <span class="text-rose-500">*</span></label>
                    
                    <!-- Hidden Real Input -->
                    <input type="file" name="payslip_file" id="payslip_file_input" accept=".pdf" required class="hidden" onchange="handleFileSelected(this, 'payslip_dropzone', 'payslip_info_box', 'payslip_progress_bar', 'payslip_file_name', 'payslip_file_size', 'payslip_status_text')">
                    
                    <!-- Premium Dropzone Box -->
                    <div id="payslip_dropzone" onclick="document.getElementById('payslip_file_input').click()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-950 transition-all hover:bg-slate-100/50 dark:hover:bg-slate-900/30 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        <div class="text-[11px] font-bold text-slate-700 dark:text-slate-350">Pilih atau Seret File Slip Gaji</div>
                        <div class="text-[9px] text-slate-400 dark:text-slate-500">PDF saja (Maks. 500KB)</div>
                    </div>

                    <!-- Premium Info & Progress Box -->
                    <div id="payslip_info_box" class="hidden p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl flex-col gap-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                <div class="flex flex-col min-w-0 text-left">
                                    <span id="payslip_file_name" class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2">file.pdf</span>
                                    <span id="payslip_file_size" class="text-[9px] text-slate-400 dark:text-slate-500 font-mono">0 KB</span>
                                </div>
                            </div>
                            <button type="button" onclick="clearFileSelection('payslip_file_input', 'payslip_dropzone', 'payslip_info_box', 'payslip_progress_bar')" class="text-slate-400 hover:text-rose-500 transition-colors cursor-pointer border-0 bg-transparent shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-1.5">
                            <!-- Progress Bar Container -->
                            <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div id="payslip_progress_bar" class="bg-indigo-600 h-full w-0 transition-all duration-300 ease-out rounded-full"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">
                                <span id="payslip_status_text">Mengunggah...</span>
                                <span class="font-mono">100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">File Lampiran Tambahan <span class="text-slate-400 dark:text-slate-500 font-medium">(Opsional)</span></label>
                    
                    <!-- Hidden Real Input -->
                    <input type="file" name="attachment_file" id="attachment_file_input" accept=".pdf,.png,.jpg,.jpeg" class="hidden" onchange="handleFileSelected(this, 'attachment_dropzone', 'attachment_info_box', 'attachment_progress_bar', 'attachment_file_name', 'attachment_file_size', 'attachment_status_text')">
                    
                    <!-- Premium Dropzone Box -->
                    <div id="attachment_dropzone" onclick="document.getElementById('attachment_file_input').click()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-950 transition-all hover:bg-slate-100/50 dark:hover:bg-slate-900/30 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        <div class="text-[11px] font-bold text-slate-700 dark:text-slate-350">Pilih atau Seret Lampiran</div>
                        <div class="text-[9px] text-slate-400 dark:text-slate-500">PDF atau Gambar (Maks. 2MB)</div>
                    </div>

                    <!-- Premium Info & Progress Box -->
                    <div id="attachment_info_box" class="hidden p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl flex-col gap-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <div class="flex flex-col min-w-0 text-left">
                                    <span id="attachment_file_name" class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2">file.png</span>
                                    <span id="attachment_file_size" class="text-[9px] text-slate-400 dark:text-slate-500 font-mono">0 KB</span>
                                </div>
                            </div>
                            <button type="button" onclick="clearFileSelection('attachment_file_input', 'attachment_dropzone', 'attachment_info_box', 'attachment_progress_bar')" class="text-slate-400 hover:text-rose-500 transition-colors cursor-pointer border-0 bg-transparent shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-1.5">
                            <!-- Progress Bar Container -->
                            <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div id="attachment_progress_bar" class="bg-indigo-600 h-full w-0 transition-all duration-300 ease-out rounded-full"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">
                                <span id="attachment_status_text">Mengunggah...</span>
                                <span class="font-mono">100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeUploadModal()"
                            class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-750 dark:text-slate-300 rounded-xl transition-all font-bold text-xs cursor-pointer shadow-3xs">Batal</button>
                    <button type="submit"
                            class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all font-bold text-xs cursor-pointer shadow-2xs hover:scale-[1.02] duration-150 border-0">Upload File</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formatIndonesianMonth(yearMonth) {
            if (!yearMonth) return '';
            const [year, month] = yearMonth.split('-');
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const monthIndex = parseInt(month, 10) - 1;
            return (months[monthIndex] || '') + ' ' + year;
        }

        function openUploadModal(btn) {
            const monthInput = document.querySelector('input[name="month"]');
            const selectedMonth = (monthInput && monthInput.value) ? monthInput.value : '{{ $month }}';

            const empId = btn.getAttribute('data-employee-id') || '';
            const unitId = btn.getAttribute('data-unit-id') || '';
            const empName = btn.getAttribute('data-employee-name') || '';
            const hasPayslip = btn.getAttribute('data-has-payslip') === 'true';
            const payslipUrl = btn.getAttribute('data-payslip-url') || '';
            const attachmentUrl = btn.getAttribute('data-attachment-url') || '';
            const payslipName = btn.getAttribute('data-payslip-name') || '';
            const attachmentName = btn.getAttribute('data-attachment-name') || '';

            const empIdEl = document.getElementById('modal_employee_id');
            const unitIdEl = document.getElementById('modal_unit_id');
            const empNameEl = document.getElementById('modal_employee_name');
            const redirectUrlEl = document.getElementById('modal_redirect_url');

            if (empIdEl) empIdEl.value = empId;
            if (unitIdEl) unitIdEl.value = unitId;
            if (empNameEl) empNameEl.innerText = empName;
            if (redirectUrlEl) redirectUrlEl.value = window.location.href;

            // Dynamically update form period input & title text to match selected filter month
            const uploadForm = document.getElementById('uploadForm');
            if (uploadForm) {
                const periodInput = uploadForm.querySelector('input[name="period"]');
                if (periodInput) periodInput.value = selectedMonth;
            }

            const formattedMonth = formatIndonesianMonth(selectedMonth);
            const titleEl = document.getElementById('modal_period_title');
            const badgeEl = document.getElementById('modal_period_badge');
            const codeEl = document.getElementById('modal_period_code');

            if (titleEl) titleEl.innerText = formattedMonth;
            if (badgeEl) badgeEl.innerText = formattedMonth;
            if (codeEl) codeEl.innerText = selectedMonth;

            const fileInput = document.getElementById('payslip_file_input');
            const dropzone = document.getElementById('payslip_dropzone');
            const infoBox = document.getElementById('payslip_info_box');
            const progressBar = document.getElementById('payslip_progress_bar');
            const statusText = document.getElementById('payslip_status_text');
            
            const attachInput = document.getElementById('attachment_file_input');
            const attachDropzone = document.getElementById('attachment_dropzone');
            const attachInfoBox = document.getElementById('attachment_info_box');
            const attachProgressBar = document.getElementById('attachment_progress_bar');
            const attachStatusText = document.getElementById('attachment_status_text');

            if (hasPayslip) {
                // Edit mode: make main payslip file input optional
                if (fileInput) fileInput.removeAttribute('required');
                
                // Show existing payslip file info
                if (dropzone) dropzone.classList.add('hidden');
                if (infoBox) {
                    infoBox.classList.remove('hidden');
                    infoBox.classList.add('flex');
                }
                const fileNameEl = document.getElementById('payslip_file_name');
                if (fileNameEl) {
                    fileNameEl.outerHTML = `<a href="${payslipUrl}" target="_blank" id="payslip_file_name" class="text-xs font-bold text-indigo-650 hover:underline dark:text-indigo-400 truncate pr-2">${payslipName}</a>`;
                }
                const fileSizeEl = document.getElementById('payslip_file_size');
                if (fileSizeEl) fileSizeEl.innerText = 'Sudah Diunggah';
                if (progressBar) progressBar.style.width = '100%';
                if (statusText) {
                    statusText.innerText = 'File Eksis (Akan Dipertahankan)';
                    statusText.className = 'text-[9px] text-emerald-500 font-bold uppercase tracking-wider';
                }

                // Show existing attachment if exists
                if (attachmentUrl && attachmentName) {
                    if (attachDropzone) attachDropzone.classList.add('hidden');
                    if (attachInfoBox) {
                        attachInfoBox.classList.remove('hidden');
                        attachInfoBox.classList.add('flex');
                    }
                    const attachNameEl = document.getElementById('attachment_file_name');
                    if (attachNameEl) {
                        attachNameEl.outerHTML = `<a href="${attachmentUrl}" target="_blank" id="attachment_file_name" class="text-xs font-bold text-indigo-650 hover:underline dark:text-indigo-400 truncate pr-2">${attachmentName}</a>`;
                    }
                    const attachSizeEl = document.getElementById('attachment_file_size');
                    if (attachSizeEl) attachSizeEl.innerText = 'Sudah Diunggah';
                    if (attachProgressBar) attachProgressBar.style.width = '100%';
                    if (attachStatusText) {
                        attachStatusText.innerText = 'File Eksis (Akan Dipertahankan)';
                        attachStatusText.className = 'text-[9px] text-emerald-500 font-bold uppercase tracking-wider';
                    }
                } else {
                    // Reset attachment fields
                    if (attachInput) attachInput.value = '';
                    if (attachDropzone) attachDropzone.classList.remove('hidden');
                    if (attachInfoBox) {
                        attachInfoBox.classList.add('hidden');
                        attachInfoBox.classList.remove('flex');
                    }
                    const attachNameEl = document.getElementById('attachment_file_name');
                    if (attachNameEl) {
                        attachNameEl.outerHTML = `<span id="attachment_file_name" class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2">file.png</span>`;
                    }
                    const attachSizeEl = document.getElementById('attachment_file_size');
                    if (attachSizeEl) attachSizeEl.innerText = '0 KB';
                }
            } else {
                // Create mode: make main payslip file input required
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');
                    fileInput.value = '';
                }
                
                // Reset payslip fields
                if (dropzone) dropzone.classList.remove('hidden');
                if (infoBox) {
                    infoBox.classList.add('hidden');
                    infoBox.classList.remove('flex');
                }
                const fileNameEl = document.getElementById('payslip_file_name');
                if (fileNameEl) {
                    fileNameEl.outerHTML = `<span id="payslip_file_name" class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2">file.pdf</span>`;
                }
                const fileSizeEl = document.getElementById('payslip_file_size');
                if (fileSizeEl) fileSizeEl.innerText = '0 KB';

                // Reset attachment fields
                if (attachInput) attachInput.value = '';
                if (attachDropzone) attachDropzone.classList.remove('hidden');
                if (attachInfoBox) {
                    attachInfoBox.classList.add('hidden');
                    attachInfoBox.classList.remove('flex');
                }
                const attachNameEl = document.getElementById('attachment_file_name');
                if (attachNameEl) {
                    attachNameEl.outerHTML = `<span id="attachment_file_name" class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2">file.png</span>`;
                }
                const attachSizeEl = document.getElementById('attachment_file_size');
                if (attachSizeEl) attachSizeEl.innerText = '0 KB';
            }

            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            if (modal && content) {
                modal.classList.remove('hidden');
                void modal.offsetWidth;
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }

        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('uploadForm').reset();
                
                // Clear selection styling for both inputs
                clearFileSelection('payslip_file_input', 'payslip_dropzone', 'payslip_info_box', 'payslip_progress_bar');
                clearFileSelection('attachment_file_input', 'attachment_dropzone', 'attachment_info_box', 'attachment_progress_bar');
            }, 300);
        }

        // Close modal when clicking outside the content box
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUploadModal();
            }
        });

        // Premium dynamic file inputs handlers
        function handleFileSelected(input, dropzoneId, infoBoxId, progressBarId, fileNameId, fileSizeId, statusTextId) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Show Name and Size
                document.getElementById(fileNameId).innerText = file.name;
                
                let sizeStr = (file.size / 1024).toFixed(1) + ' KB';
                if (file.size > 1024 * 1024) {
                    sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                }
                document.getElementById(fileSizeId).innerText = sizeStr;

                // Hide dropzone and show progress box
                document.getElementById(dropzoneId).classList.add('hidden');
                const infoBox = document.getElementById(infoBoxId);
                infoBox.classList.remove('hidden');
                infoBox.classList.add('flex');

                // Animate Progress Bar
                const progressBar = document.getElementById(progressBarId);
                const statusText = document.getElementById(statusTextId);
                progressBar.style.width = '0%';
                statusText.innerText = 'Membaca file...';
                statusText.className = 'text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider';

                setTimeout(() => {
                    progressBar.style.width = '50%';
                    statusText.innerText = 'Memverifikasi berkas...';
                    
                    setTimeout(() => {
                        progressBar.style.width = '100%';
                        statusText.innerText = 'Siap Diunggah!';
                        statusText.className = 'text-[9px] text-emerald-500 font-bold uppercase tracking-wider';
                    }, 350);
                }, 200);
            }
        }

        function clearFileSelection(inputId, dropzoneId, infoBoxId, progressBarId) {
            const input = document.getElementById(inputId);
            input.value = ''; // Reset input value
            
            document.getElementById(dropzoneId).classList.remove('hidden');
            const infoBox = document.getElementById(infoBoxId);
            infoBox.classList.add('hidden');
            infoBox.classList.remove('flex');
            
            const progressBar = document.getElementById(progressBarId);
            progressBar.style.width = '0%';
        }
    </script>
</x-admin-layout>

<!-- AJAX NAVIGATION & FILTER SCRIPT -->
<script>
    function triggerFilterForm(el) {
        const form = document.getElementById('payslip-filter-form');
        if (form) {
            form.requestSubmit();
        }
    }

    function selectUnitFilter(unitId, btnEl) {
        const input = document.getElementById('filter-unit-id');
        if (input) {
            input.value = unitId;
            triggerFilterForm(btnEl);
        }
    }

    function selectMonthFilter(monthVal, btnEl) {
        const input = document.getElementById('filter-month-input') || document.querySelector('input[name="month"]');
        if (input) {
            input.value = monthVal;
            triggerFilterForm(btnEl || input);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('payslip-report-container');

        function loadTableContent(url) {
            if (container) {
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';
            }
            
            if (typeof NProgress !== 'undefined') {
                NProgress.start();
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('payslip-report-container');
                
                if (newContent && container) {
                    container.innerHTML = newContent.innerHTML;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';

                    // Reinitialize Lucide Icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Sync URL in address bar without reload
                    window.history.pushState({}, '', url);
                } else {
                    window.location.href = url;
                }
            })
            .catch(err => {
                console.error('AJAX loading failed:', err);
                window.location.href = url;
            })
            .finally(() => {
                if (typeof NProgress !== 'undefined') {
                    NProgress.done();
                }
                const globalLoader = document.getElementById('global-loading-overlay');
                if (globalLoader) {
                    globalLoader.classList.add('hidden');
                }
            });
        }

        // Delegate submit event
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('#payslip-filter-form');
            if (!form) return;

            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const action = form.getAttribute('action') || window.location.pathname;
            const url = new URL(action, window.location.origin);
            url.search = params.toString();

            loadTableContent(url);
        });

        // Delegate click on pagination links
        document.addEventListener('click', function (e) {
            const link = e.target.closest('#payslip-report-container a');
            if (!link) return;

            // Don't intercept download/export buttons or external links
            if (link.getAttribute('target') === '_blank' || link.getAttribute('data-no-loader') === 'true') {
                if (link.classList.contains('reset-filter-btn') && link.getAttribute('href')) {
                    e.preventDefault();
                    loadTableContent(link.getAttribute('href'));
                }
                return;
            }

            const href = link.getAttribute('href');
            if (href && (href.startsWith(window.location.origin) || href.startsWith('/'))) {
                // Ignore storage links
                if (href.includes('/storage/')) {
                    return;
                }
                e.preventDefault();
                loadTableContent(href);
            }
        });
    });
</script>

<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 280px !important;
        }
    }
</style>
