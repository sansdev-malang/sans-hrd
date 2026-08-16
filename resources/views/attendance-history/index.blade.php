<x-admin-layout>
    <div class="p-6 space-y-6">
        
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Riwayat Kehadiran Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Melihat, memantau, dan menyaring riwayat log absensi harian pegawai secara terperinci.</p>
            </div>
            
            <!-- EXPORT DROPDOWN -->
            <div x-data="{ open: false }" class="relative inline-block text-left w-full md:w-auto">
                <button type="button" @click="open = !open" @click.outside="open = false" class="w-full md:w-auto justify-center h-9 px-4 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor Laporan</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                </button>
                
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                    <a href="{{ route('attendance-history.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                        Excel (.xlsx)
                    </a>
                    <a href="{{ route('attendance-history.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                        <i data-lucide="file-text" class="w-4 h-4 text-rose-600 dark:text-rose-500"></i>
                        PDF (.pdf)
                    </a>
                </div>
            </div>
        </section>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('attendance-history.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                    
                    <!-- Search Pegawai -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cari Pegawai</label>
                        <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                            <div class="pl-3 text-slate-400 dark:text-slate-500">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="search" x-model="searchVal" placeholder="Nama atau NIP..."
                                style="border: none !important; outline: none !important; box-shadow: none !important;"
                                class="w-full h-9 px-2 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-0">
                            <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('form').submit();" class="h-9 px-2.5 text-slate-400 hover:text-slate-650 dark:hover:text-slate-250 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mulai Tanggal -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDateReq) }}" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Selesai Tanggal -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Selesai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDateReq) }}" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Status Kehadiran -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Status Kehadiran</label>
                        <select name="status" class="w-full text-xs h-9 px-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="Hadir" {{ request('status') === 'Hadir' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                            <option value="Terlambat" {{ request('status') === 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="Alfa" {{ request('status') === 'Alfa' ? 'selected' : '' }}>Alfa</option>
                            <option value="Sakit" {{ request('status') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="Izin" {{ request('status') === 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Cuti" {{ request('status') === 'Cuti' ? 'selected' : '' }}>Cuti</option>
                            <option value="Dinas" {{ request('status') === 'Dinas' ? 'selected' : '' }}>Dinas / Tugas</option>
                            <option value="Off" {{ request('status') === 'Off' ? 'selected' : '' }}>Libur Pekan (Off)</option>
                            <option value="Libur" {{ request('status') === 'Libur' ? 'selected' : '' }}>Libur Resmi</option>
                            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending (Belum Shift)</option>
                        </select>
                    </div>

                    <!-- Unit Sekolah -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Unit Sekolah</label>
                        <select name="unit_id" class="w-full text-xs h-9 px-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="">Semua Unit</option>
                            @foreach($schoolUnits as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    
                    <!-- Per Page & Info -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500">Tampilkan</span>
                        <select name="per_page" onchange="this.form.submit()" class="text-xs h-8 px-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-md text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                            <option value="200" {{ request('per_page') == '200' ? 'selected' : '' }}>200 baris</option>
                        </select>
                        <span class="text-xs text-slate-500">dari total {{ $paginatedHistory->total() }} entri</span>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center gap-2 justify-end">
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-250 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            Terapkan Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'start_date', 'end_date', 'status', 'unit_id', 'position', 'per_page']))
                            <a href="{{ route('attendance-history.index') }}" class="inline-flex items-center justify-center h-9 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors gap-1.5">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        <!-- TABLE SECTION -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-slate-500 dark:text-slate-400 font-semibold">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Pegawai</th>
                            <th class="px-4 py-3 text-left">Hari & Tanggal</th>
                            <th class="px-4 py-3 text-left">Jadwal Shift</th>
                            <th class="px-4 py-3 text-center">Scan Masuk</th>
                            <th class="px-4 py-3 text-center">Scan Keluar</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left max-w-xs">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($paginatedHistory as $index => $row)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="px-4 py-3 text-center font-medium text-slate-400 dark:text-slate-500">
                                    {{ ($paginatedHistory->currentPage() - 1) * $paginatedHistory->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $row['employee_name'] }}</div>
                                    @if($row['employee_nip'])
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mb-0.5">{{ $row['employee_nip'] }}</div>
                                    @endif
                                    <div class="text-[10px] text-slate-500 dark:text-slate-450">{{ $row['unit_name'] }} - {{ $row['position'] }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">
                                    {{ $row['date_formatted'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 dark:text-slate-300">{{ $row['shift_name'] }}</div>
                                    @if($row['shift_start'])
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                            {{ $row['shift_start'] }} - {{ $row['shift_end'] }}
                                        </div>
                                    @else
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Libur</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium">
                                    @if($row['check_in'])
                                        <span class="text-slate-800 dark:text-slate-105">{{ $row['check_in'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium">
                                    @if($row['check_out'])
                                        <span class="text-slate-800 dark:text-slate-105">{{ $row['check_out'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusClass = match($row['status']) {
                                            'Hadir' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-450 border-emerald-250 dark:border-emerald-800/40',
                                            'Terlambat' => 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-500 border-amber-200 dark:border-amber-900/30',
                                            'Alfa' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-450 border-rose-250 dark:border-rose-900/40',
                                            'Sakit' => 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-450 border-red-200 dark:border-red-900/40',
                                            'Izin' => 'bg-orange-50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-450 border-orange-200 dark:border-orange-900/30',
                                            'Cuti' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-450 border-blue-200 dark:border-blue-900/40',
                                            'Dinas' => 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border-indigo-200/30 dark:border-indigo-900/30',
                                            'Off' => 'bg-slate-50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-800',
                                            'Libur' => 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800',
                                            'Pending' => 'bg-slate-50 dark:bg-slate-950 text-slate-400 border-slate-100 dark:border-slate-900',
                                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $row['notes'] }}">
                                    @if($row['status'] === 'Terlambat' && $row['late_minutes'] > 0)
                                        <span class="text-amber-600 dark:text-amber-500 font-semibold">Terlambat {{ $row['late_minutes'] }} menit</span>
                                    @elseif($row['notes'])
                                        <span class="font-medium">{{ $row['notes'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700 font-mono">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="clipboard-x" class="w-8 h-8 opacity-40"></i>
                                        <span class="text-sm font-semibold">Tidak Ada Data Riwayat Kehadiran</span>
                                        <span class="text-xs">Silakan sesuaikan filter tanggal atau kata kunci pencarian Anda.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            @if($paginatedHistory->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $paginatedHistory->links('pagination::simple-tailwind') }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-slate-500">
                                Menampilkan
                                <span class="font-medium text-slate-700 dark:text-slate-350">{{ $paginatedHistory->firstItem() ?? 0 }}</span>
                                sampai
                                <span class="font-medium text-slate-700 dark:text-slate-350">{{ $paginatedHistory->lastItem() ?? 0 }}</span>
                                dari
                                <span class="font-medium text-slate-700 dark:text-slate-350">{{ $paginatedHistory->total() }}</span>
                                hasil
                            </p>
                        </div>
                        <div>
                            {{ $paginatedHistory->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            @endif
        </section>

    </div>
</x-admin-layout>
