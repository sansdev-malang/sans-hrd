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
                                :class="searchVal.trim() !== '' ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'bg-slate-100 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300'"
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

        <!-- MAIN TABLE WITH INTEGRATED PERIOD HEADER -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden w-full p-0">
            <!-- Integrated Card Header -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 via-indigo-50/20 to-slate-50 dark:from-slate-900 dark:via-indigo-950/20 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="calendar-check-2" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 font-nasalization tracking-wide">
                                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                            </h3>
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
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Semua slip gaji dan lampiran yang diunggah tercatat untuk masa kerja <strong class="text-indigo-600 dark:text-indigo-400">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-200/80 dark:border-slate-700/80 shadow-3xs font-medium">
                        Total: <strong class="text-slate-800 dark:text-slate-200">{{ $paginatedEmployees instanceof \Illuminate\Pagination\LengthAwarePaginator ? $paginatedEmployees->total() : count($paginatedEmployees) }}</strong> Pegawai
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto" id="payslip-table-scroll-container" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                <table class="w-full text-sm text-left" id="payslip-main-table">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 uppercase font-semibold border-b border-slate-200 dark:border-slate-800 sticky top-0 z-20">
                        <tr>
                            <th class="px-5 py-3.5 min-w-[210px]">Profil Pegawai</th>
                            <th class="px-4 py-3.5 min-w-[110px]">Periode</th>
                            <th class="px-5 py-3.5 min-w-[190px]">Lampiran (Opsional)</th>
                            <th class="px-5 py-3.5 min-w-[260px]">Slip Gaji (PDF)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="payslip-table-tbody">
                        @forelse($paginatedEmployees as $index => $emp)
                            @php
                                $empName = $emp['name'] ?? 'Tidak Diketahui';
                                $color = $colors[$index % count($colors)];
                                $initial = getInitials($empName);
                                $rowId = 'emp-row-' . $emp['unit_id'] . '-' . $emp['id'];
                                $payslip = $emp['payslip'] ?? null;
                            @endphp
                            <tr class="payslip-row group hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-all duration-150 relative"
                                id="{{ $rowId }}"
                                data-employee-id="{{ $emp['id'] }}"
                                data-unit-id="{{ $emp['unit_id'] }}"
                                data-employee-name="{{ $empName }}"
                                data-nik="{{ $emp['nik'] ?? $emp['nuptk_nip_nik'] ?? '' }}"
                                data-nuptk="{{ $emp['nuptk'] ?? '' }}"
                                data-niy="{{ $emp['niy'] ?? '' }}"
                                data-period="{{ $month }}">
                                
                                <td class="px-5 py-3.5">
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

                                <td class="px-4 py-3.5 text-xs font-bold text-slate-605 dark:text-slate-300 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                                </td>

                                <!-- LAMPIRAN CELL (KOLOM KE-3) -->
                                <td class="px-5 py-3.5 payslip-attachment-cell">
                                    @if($payslip && $payslip->attachment_path)
                                        <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700/80 shadow-3xs max-w-full">
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                            <a href="{{ Storage::url($payslip->attachment_path) }}" target="_blank"
                                               class="text-xs font-medium text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 truncate max-w-[120px] transition-colors"
                                               title="{{ $payslip->original_attachment_name ?? basename($payslip->attachment_path) }} (Klik untuk melihat)">
                                                {{ $payslip->original_attachment_name ?? basename($payslip->attachment_path) }}
                                            </a>
                                            <div class="flex items-center gap-0.5 ml-1 pl-1 border-l border-slate-200 dark:border-slate-700 shrink-0">
                                                <label class="btn-replace-attachment p-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded transition-colors cursor-pointer" title="Ganti File Lampiran">
                                                    <i data-lucide="upload" class="w-3 h-3"></i>
                                                    <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                                                </label>
                                                <button type="button"
                                                        onclick="handleDirectDelete(this, '{{ route('payslips.destroyAttachment', $payslip->id) }}', 'attachment')"
                                                        class="btn-delete-attachment p-1 hover:bg-rose-100 dark:hover:bg-rose-950/60 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition-colors cursor-pointer" title="Hapus Lampiran">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <label class="attachment-dropzone-btn inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-xs font-medium transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Unggah Berkas Pendukung (PDF/Gambar)">
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                            <span>+ Lampiran</span>
                                            <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                                        </label>
                                    @endif
                                </td>

                                <!-- SLIP GAJI CELL (KOLOM KE-4 / TERAKHIR) -->
                                <td class="px-5 py-3.5 payslip-slip-cell">
                                    @if($payslip && $payslip->file_path)
                                        <div class="inline-flex items-center gap-2 bg-emerald-50/60 dark:bg-emerald-950/20 px-2.5 py-1.5 rounded-lg border border-emerald-200/70 dark:border-emerald-900/50 shadow-3xs max-w-full">
                                            <div class="flex items-center gap-1.5 min-w-0 pr-1">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <a href="{{ Storage::url($payslip->file_path) }}" target="_blank"
                                                   class="text-xs font-semibold text-emerald-900 dark:text-emerald-200 hover:underline truncate max-w-[130px]"
                                                   title="{{ $payslip->original_filename ?? basename($payslip->file_path) }} (Klik untuk membuka)">
                                                    {{ $payslip->original_filename ?? basename($payslip->file_path) }}
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <a href="{{ Storage::url($payslip->file_path) }}" target="_blank"
                                                   class="btn-view-payslip h-6 px-2 bg-white dark:bg-slate-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold rounded border border-emerald-200 dark:border-emerald-800/80 transition-all flex items-center gap-1 shadow-3xs" title="Buka Berkas Slip Gaji">
                                                    <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
                                                    Buka
                                                </a>
                                                <label class="btn-replace-payslip h-6 px-1.5 bg-white dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-600 dark:text-amber-400 text-[11px] font-bold rounded border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1 shadow-3xs cursor-pointer" title="Ganti Berkas Slip Gaji">
                                                    <i data-lucide="upload" class="w-2.5 h-2.5"></i>
                                                    <input type="file" accept=".pdf" class="hidden inline-payslip-file-input" onchange="handleDirectFileSelect(this, 'payslip')">
                                                </label>
                                                <button type="button"
                                                        onclick="handleDirectDelete(this, '{{ route('payslips.destroy', $payslip->id) }}', 'all')"
                                                        class="btn-delete-payslip h-6 px-1.5 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[11px] font-bold rounded border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1 shadow-3xs cursor-pointer" title="Hapus Slip Gaji">
                                                    <i data-lucide="trash-2" class="w-2.5 h-2.5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <label class="payslip-dropzone-btn inline-flex items-center gap-2 h-8 px-3 rounded-lg border-2 border-dashed border-indigo-200 hover:border-indigo-400 dark:border-indigo-900/60 dark:hover:border-indigo-700 bg-indigo-50/50 hover:bg-indigo-100/60 dark:bg-indigo-950/20 dark:hover:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-xs font-semibold transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Seret file PDF atau klik untuk unggah slip gaji">
                                            <i data-lucide="file-up" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 group-hover:-translate-y-0.5 transition-transform"></i>
                                            <span>Upload PDF Slip Gaji</span>
                                            <input type="file" accept=".pdf" class="hidden inline-payslip-file-input" onchange="handleDirectFileSelect(this, 'payslip')">
                                        </label>
                                    @endif
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

    <!-- INLINE DRAG & DROP & DIRECT AJAX PAYSLIP SCRIPT -->
    <script>
        // Prevent browser default open-file behavior on whole window when dragging files
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            window.addEventListener(eventName, function (e) {
                if (e.dataTransfer && e.dataTransfer.types && Array.from(e.dataTransfer.types).includes('Files')) {
                    e.preventDefault();
                }
            }, false);
        });

        // Direct Inline File Selection Handler
        function handleDirectFileSelect(inputEl, type = 'payslip') {
            if (!inputEl.files || !inputEl.files[0]) return;
            const file = inputEl.files[0];
            const row = inputEl.closest('.payslip-row');
            if (row) {
                uploadSingleFileDirect(file, row, type);
            }
            inputEl.value = ''; // Reset for subsequent selections
        }

        // Direct Upload Handler via AJAX
        function uploadSingleFileDirect(file, row, type = 'auto') {
            if (!file || !row) return;

            const isPdf = file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf';
            const isImage = /\.(png|jpe?g)$/i.test(file.name) || /^image\/(png|jpe?g)$/i.test(file.type);

            // Auto-detect type if not explicitly passed
            if (type === 'auto') {
                if (isImage) {
                    type = 'attachment';
                } else if (isPdf) {
                    type = 'payslip';
                } else {
                    if (typeof showSessionToast === 'function') {
                        showSessionToast('Format Salah', 'Format file tidak didukung. Gunakan PDF untuk Slip Gaji, atau PDF/Gambar untuk Lampiran.', 'error');
                    }
                    return;
                }
            }

            // Specific validations
            if (type === 'payslip' && !isPdf) {
                if (typeof showSessionToast === 'function') {
                    showSessionToast('Format Salah', 'Slip gaji utama harus berupa dokumen PDF (.pdf)', 'error');
                }
                return;
            }

            if (type === 'attachment' && !isPdf && !isImage) {
                if (typeof showSessionToast === 'function') {
                    showSessionToast('Format Salah', 'Lampiran harus berupa dokumen PDF atau Gambar (.png, .jpg, .jpeg)', 'error');
                }
                return;
            }

            // Max 5MB
            if (file.size > 5 * 1024 * 1024) {
                if (typeof showSessionToast === 'function') {
                    showSessionToast('File Terlalu Besar', 'Ukuran file maksimal adalah 5MB.', 'error');
                }
                return;
            }

            const employeeId = row.getAttribute('data-employee-id');
            const unitId = row.getAttribute('data-unit-id');
            const employeeName = row.getAttribute('data-employee-name') || 'Pegawai';
            const period = row.getAttribute('data-period') || '{{ $month }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            const targetCell = type === 'attachment' ? row.querySelector('.payslip-attachment-cell') : row.querySelector('.payslip-slip-cell');
            const prevTargetHtml = targetCell ? targetCell.innerHTML : '';

            // UI Loading indicator in target cell
            if (targetCell) {
                targetCell.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold border border-indigo-200/50 dark:border-indigo-800/50 shadow-3xs animate-pulse">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Mengunggah...</span>
                        </div>
                    </div>
                `;
            }
            row.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('employee_id', employeeId);
            formData.append('school_unit_id', unitId);
            formData.append('period', period);
            if (type === 'attachment') {
                formData.append('attachment_file', file);
            } else {
                formData.append('payslip_file', file);
            }

            fetch('{{ route('payslips.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengunggah berkas.');
                }
                return data;
            })
            .then(data => {
                const p = data.payslip;
                if (!p) return;

                // 1. Update Slip Gaji Cell if uploaded or exists
                const slipCell = row.querySelector('.payslip-slip-cell');
                if (slipCell && p.file_url) {
                    const originalName = p.original_filename || 'slip_gaji.pdf';
                    const destroyUrl = `/payslips/${p.id}`;
                    slipCell.innerHTML = `
                        <div class="inline-flex items-center gap-2 bg-emerald-50/60 dark:bg-emerald-950/20 px-2.5 py-1.5 rounded-lg border border-emerald-200/70 dark:border-emerald-900/50 shadow-3xs max-w-full">
                            <div class="flex items-center gap-1.5 min-w-0 pr-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <a href="${p.file_url}" target="_blank"
                                   class="text-xs font-semibold text-emerald-900 dark:text-emerald-200 hover:underline truncate max-w-[130px]"
                                   title="${originalName} (Klik untuk membuka)">
                                    ${originalName}
                                </a>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <a href="${p.file_url}" target="_blank"
                                   class="btn-view-payslip h-6 px-2 bg-white dark:bg-slate-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold rounded border border-emerald-200 dark:border-emerald-800/80 transition-all flex items-center gap-1 shadow-3xs" title="Buka Berkas Slip Gaji">
                                    <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
                                    Buka
                                </a>
                                <label class="btn-replace-payslip h-6 px-1.5 bg-white dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-amber-955/40 text-amber-600 dark:text-amber-400 text-[11px] font-bold rounded border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1 shadow-3xs cursor-pointer" title="Ganti Berkas Slip Gaji">
                                    <i data-lucide="upload" class="w-2.5 h-2.5"></i>
                                    <input type="file" accept=".pdf" class="hidden inline-payslip-file-input" onchange="handleDirectFileSelect(this, 'payslip')">
                                </label>
                                <button type="button"
                                        onclick="handleDirectDelete(this, '${destroyUrl}', 'all')"
                                        class="btn-delete-payslip h-6 px-1.5 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[11px] font-bold rounded border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1 shadow-3xs cursor-pointer" title="Hapus Slip Gaji">
                                    <i data-lucide="trash-2" class="w-2.5 h-2.5"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }

                // 2. Update Lampiran Cell
                const attachCell = row.querySelector('.payslip-attachment-cell');
                if (attachCell) {
                    if (p.attachment_url) {
                        const originalAttachName = p.original_attachment_name || 'lampiran';
                        const destroyAttachUrl = `/payslips/${p.id}/attachment`;
                        attachCell.innerHTML = `
                            <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700/80 shadow-3xs max-w-full">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                <a href="${p.attachment_url}" target="_blank"
                                   class="text-xs font-medium text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 truncate max-w-[120px] transition-colors"
                                   title="${originalAttachName} (Klik untuk melihat)">
                                    ${originalAttachName}
                                </a>
                                <div class="flex items-center gap-0.5 ml-1 pl-1 border-l border-slate-200 dark:border-slate-700 shrink-0">
                                    <label class="btn-replace-attachment p-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded transition-colors cursor-pointer" title="Ganti File Lampiran">
                                        <i data-lucide="upload" class="w-3 h-3"></i>
                                        <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                                    </label>
                                    <button type="button"
                                            onclick="handleDirectDelete(this, '${destroyAttachUrl}', 'attachment')"
                                            class="btn-delete-attachment p-1 hover:bg-rose-100 dark:hover:bg-rose-950/60 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition-colors cursor-pointer" title="Hapus Lampiran">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    } else if (type === 'attachment') {
                        // Reset to empty attachment dropzone
                        attachCell.innerHTML = `
                            <label class="attachment-dropzone-btn inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-xs font-medium transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Unggah Berkas Pendukung (PDF/Gambar)">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                <span>+ Lampiran</span>
                                <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                            </label>
                        `;
                    }
                }

                if (window.lucide) {
                    lucide.createIcons();
                }

                // Visual flash highlight
                row.classList.add('bg-emerald-50/80', 'dark:bg-emerald-950/40');
                setTimeout(() => {
                    row.classList.remove('bg-emerald-50/80', 'dark:bg-emerald-950/40');
                }, 1800);

                if (typeof showSessionToast === 'function') {
                    const label = type === 'attachment' ? 'Lampiran' : 'Slip gaji';
                    showSessionToast('Sukses!', `${label} ${employeeName} berhasil diunggah.`, 'success');
                }
            })
            .catch(err => {
                if (targetCell) {
                    targetCell.innerHTML = prevTargetHtml;
                }
                if (window.lucide) {
                    lucide.createIcons();
                }
                if (typeof showSessionToast === 'function') {
                    showSessionToast('Gagal Mengunggah', err.message || 'Terjadi kesalahan jaringan.', 'error');
                }
            })
            .finally(() => {
                row.style.pointerEvents = 'auto';
            });
        }

        // Direct Delete Handler (all payslip OR attachment only)
        function handleDirectDelete(btnEl, deleteUrl, mode = 'all') {
            const isAttachment = mode === 'attachment';
            const confirmMsg = isAttachment ? 'Apakah Anda yakin ingin menghapus lampiran pegawai ini?' : 'Apakah Anda yakin ingin menghapus slip gaji pegawai ini?';

            if (!confirm(confirmMsg)) {
                return;
            }

            const row = btnEl.closest('.payslip-row');
            if (!row) return;

            const targetCell = isAttachment ? row.querySelector('.payslip-attachment-cell') : row.querySelector('.payslip-slip-cell');
            const prevHtml = targetCell ? targetCell.innerHTML : '';

            if (targetCell) {
                targetCell.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-200/50 dark:border-rose-800/50 shadow-3xs animate-pulse">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Menghapus...</span>
                        </div>
                    </div>
                `;
            }
            row.style.pointerEvents = 'none';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menghapus berkas.');
                }
                return data;
            })
            .then(data => {
                if (isAttachment) {
                    // Reset attachment cell
                    const attachCell = row.querySelector('.payslip-attachment-cell');
                    if (attachCell) {
                        attachCell.innerHTML = `
                            <label class="attachment-dropzone-btn inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-xs font-medium transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Unggah Berkas Pendukung (PDF/Gambar)">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                <span>+ Lampiran</span>
                                <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                            </label>
                        `;
                    }
                } else {
                    // Reset slip cell
                    const slipCell = row.querySelector('.payslip-slip-cell');
                    if (slipCell) {
                        slipCell.innerHTML = `
                            <label class="payslip-dropzone-btn inline-flex items-center gap-2 h-8 px-3 rounded-lg border-2 border-dashed border-indigo-200 hover:border-indigo-400 dark:border-indigo-900/60 dark:hover:border-indigo-700 bg-indigo-50/50 hover:bg-indigo-100/60 dark:bg-indigo-950/20 dark:hover:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-xs font-semibold transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Seret file PDF atau klik untuk unggah slip gaji">
                                <i data-lucide="file-up" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 group-hover:-translate-y-0.5 transition-transform"></i>
                                <span>Upload PDF Slip Gaji</span>
                                <input type="file" accept=".pdf" class="hidden inline-payslip-file-input" onchange="handleDirectFileSelect(this, 'payslip')">
                            </label>
                        `;
                    }
                    // Also reset attachment cell
                    const attachCell = row.querySelector('.payslip-attachment-cell');
                    if (attachCell) {
                        attachCell.innerHTML = `
                            <label class="attachment-dropzone-btn inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-xs font-medium transition-all hover:scale-[1.02] cursor-pointer shadow-3xs group" title="Unggah Berkas Pendukung (PDF/Gambar)">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                <span>+ Lampiran</span>
                                <input type="file" accept=".pdf,.png,.jpg,.jpeg" class="hidden inline-attachment-file-input" onchange="handleDirectFileSelect(this, 'attachment')">
                            </label>
                        `;
                    }
                }

                if (window.lucide) {
                    lucide.createIcons();
                }

                if (typeof showSessionToast === 'function') {
                    showSessionToast('Sukses!', isAttachment ? 'Lampiran berhasil dihapus.' : 'Slip gaji berhasil dihapus.', 'success');
                }
            })
            .catch(err => {
                if (targetCell) {
                    targetCell.innerHTML = prevHtml;
                }
                if (window.lucide) {
                    lucide.createIcons();
                }
                if (typeof showSessionToast === 'function') {
                    showSessionToast('Gagal Menghapus', err.message || 'Terjadi kesalahan jaringan.', 'error');
                }
            })
            .finally(() => {
                row.style.pointerEvents = 'auto';
            });
        }

        // Attach Drag-and-Drop Listeners (Direct per-row upload)
        function setupRowDragAndDrop() {
            const rows = document.querySelectorAll('.payslip-row');
            
            rows.forEach(row => {
                let dragCounter = 0;

                row.addEventListener('dragenter', function (e) {
                    e.preventDefault();
                    dragCounter++;
                    row.classList.add('bg-indigo-50/70', 'dark:bg-indigo-950/50', 'ring-2', 'ring-indigo-500', 'ring-inset');
                });

                row.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    if (e.dataTransfer) {
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                row.addEventListener('dragleave', function (e) {
                    e.preventDefault();
                    dragCounter--;
                    if (dragCounter <= 0) {
                        dragCounter = 0;
                        row.classList.remove('bg-indigo-50/70', 'dark:bg-indigo-950/50', 'ring-2', 'ring-indigo-500', 'ring-inset');
                    }
                });

                row.addEventListener('drop', function (e) {
                    e.preventDefault();
                    dragCounter = 0;
                    row.classList.remove('bg-indigo-50/70', 'dark:bg-indigo-950/50', 'ring-2', 'ring-indigo-500', 'ring-inset');

                    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        const file = e.dataTransfer.files[0];
                        
                        // Check if dropped specifically inside attachment cell or dropzone
                        const inAttachmentZone = e.target.closest('.payslip-attachment-cell') || e.target.closest('.attachment-dropzone-btn') || e.target.closest('.btn-replace-attachment');
                        const inSlipZone = e.target.closest('.payslip-slip-cell') || e.target.closest('.payslip-dropzone-btn') || e.target.closest('.btn-replace-payslip');
                        
                        let type = 'auto';
                        if (inAttachmentZone) {
                            type = 'attachment';
                        } else if (inSlipZone) {
                            type = 'payslip';
                        }

                        uploadSingleFileDirect(file, row, type);
                    }
                });
            });
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
        setupRowDragAndDrop();

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

                    // Rebind Drag and Drop handlers on new rows
                    setupRowDragAndDrop();

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
