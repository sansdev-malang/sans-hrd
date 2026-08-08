<x-admin-layout>
    <div class="p-6 space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Riwayat Absensi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Memantau waktu kedatangan dan kepulangan pegawai secara komprehensif.</p>
            </div>
            
            <!-- EXPORT DROPDOWN -->
            <div x-data="{ open: false }" class="relative w-full md:w-auto self-stretch md:self-auto flex justify-end">
                <button type="button" @click="open = !open" @click.outside="open = false" class="w-full md:w-auto justify-center h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 border border-slate-200/50 dark:border-slate-800/40 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor Data</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                </button>
                
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                    <a href="{{ route('attendance-logs.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                        Excel (.xlsx)
                    </a>
                    <a href="{{ route('attendance-logs.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                        <i data-lucide="file-text" class="w-4 h-4 text-rose-600 dark:text-rose-500"></i>
                        PDF (.pdf)
                    </a>
                </div>
            </div>
        </section>

        <!-- FILTERS & CONTROLS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('attendance-logs.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                
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
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                        <select name="unit_id" onchange="this.form.submit()"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($schoolUnits as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Jabatan -->
                    <select name="position" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>

                    @if(request()->anyFilled(['search', 'unit_id', 'position']) || request()->filled('month') && request('month') != now()->format('Y-m') || request()->filled('per_page') && request('per_page') != 15)
                        <a href="{{ route('attendance-logs.index') }}" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Right Side: Per Page Options -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-24 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="15" {{ request('per_page', 15) == '15' ? 'selected' : '' }}>15 baris</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </section>
        
        

        <!-- MAIN TABLE (MATRIX LAYOUT) -->
        @php
            $start = \Carbon\Carbon::parse($startDateReq);
            $end = \Carbon\Carbon::parse($endDateReq);
            $dates = [];
            while($start <= $end) {
                $dates[] = $start->copy();
                $start->addDay();
            }
            
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

        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            <div class="overflow-x-auto" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-4 py-3 bg-slate-50 dark:bg-slate-900 text-left sticky top-0 left-auto md:left-0 z-30 md:z-40 border-r border-slate-200 dark:border-slate-800 min-w-[150px]">
                                <div class="grid grid-cols-2">
                                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profil Pegawai</span>
                                    <span class="text-[10px] text-slate-500 font-medium whitespace-nowrap text-right">Siklus: <br><strong class="text-slate-700 dark:text-slate-300 text-xs">{{ \Carbon\Carbon::parse($startDateReq)->format('d M') }} - {{ \Carbon\Carbon::parse($endDateReq)->format('d M') }}</strong></span>
                                </div>
                            </th>

                            @foreach($dates as $date)
                            @php
                                $isToday = $date->isToday();
                                $isSunday = $date->isSunday();
                            @endphp
                            <th class="py-2 px-1 text-center sticky top-0 z-30 bg-slate-50 dark:bg-slate-900 min-w-[32px] border-r border-slate-100 dark:border-slate-800/60">
                                <div class="flex flex-col items-center justify-center gap-1 py-0.5">
                                    <span class="text-[9px] font-semibold {{ $isSunday && !$isToday ? 'text-red-400' : ($isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500') }} uppercase tracking-wider leading-none">
                                        {{ $date->translatedFormat('D') }}
                                    </span>
                                    <div class="flex items-center justify-center w-6 h-6 {{ $isToday ? 'bg-indigo-600 dark:bg-indigo-500 rounded-full' : '' }}">
                                        <span class="text-[11px] font-bold leading-none {{ $isSunday && !$isToday ? 'text-red-500 dark:text-red-400' : ($isToday ? 'text-white' : 'text-slate-700 dark:text-slate-200') }}">
                                            {{ $date->format('d') }}
                                        </span>
                                    </div>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($paginatedReports as $index => $report)
                            @php
                                $empName = $report['employee']['name'] ?? 'Tidak Diketahui';
                                $color = $colors[$index % count($colors)];
                                $initial = getInitials($empName);
                            @endphp
                            <tr class="group hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-colors">
                                <!-- KOLOM 1: PROFIL -->
                                <td class="px-4 py-2 static md:sticky md:left-0 md:z-10 bg-white dark:bg-slate-900 group-hover:bg-slate-50/60 dark:group-hover:bg-slate-900/30 border-r border-slate-100 dark:border-slate-800/60 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm" style="background:{{ $color }}">{{ $initial }}</div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ $empName }}</span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ $report['employee']['nuptk'] ?? '-' }}</span>
                                                <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300 truncate max-w-[80px]">{{ $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-') }}</span>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                
                                                                <!-- KOLOM 3+: TANGGAL -->
                                @foreach($dates as $date)
                                @php
                                    $dateStr = $date->format('Y-m-d');
                                    $detail = $report['daily_details'][$dateStr] ?? null;
                                @endphp
                                <td class="py-1 px-1 text-center border-r border-slate-50 dark:border-slate-800/30">
                                    @if($detail)
                                        @if($detail['status'] === 'Hadir')
                                            <div class="flex flex-col gap-0.5 items-center justify-center">
                                                <span class="text-[10px] font-bold {{ (!empty($detail['is_late'])) ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400' }}" title="Masuk">{{ $detail['check_in'] ?? '-' }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500" title="Pulang">{{ $detail['check_out'] ?? '-' }}</span>
                                            </div>
                                        @elseif($detail['status'] === 'Alfa')
                                            <div class="mx-auto w-full h-full min-h-[28px] flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-500 font-bold text-xs" title="Alfa">A</div>
                                        @elseif($detail['status'] === 'Libur')
                                            <div class="mx-auto w-full h-full min-h-[28px] flex items-center justify-center text-red-200 dark:text-red-900/30 font-bold text-xs" title="Libur">-</div>
                                        @elseif($detail['status'] === 'Off')
                                            <div class="mx-auto w-full h-full min-h-[28px] flex items-center justify-center bg-slate-100 dark:bg-slate-800/50 text-slate-500 font-bold text-[10px]" title="Jadwal Off">OFF</div>
                                        @elseif($detail['status'] === 'Cuti/Izin')
                                            <div class="mx-auto w-full h-full min-h-[28px] flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-500 font-bold text-[9px]" title="{{ $detail['leave_type'] ?? 'Izin/Cuti' }}">{{ $detail['leave_type'] ?? 'IZIN' }}</div>
                                        @else
                                            <div class="text-xs text-slate-300">-</div>
                                        @endif
                                    @else
                                        @if($date->isSunday())
                                            <div class="mx-auto flex items-center justify-center text-red-200 dark:text-red-900/30 font-bold text-xs" title="Minggu">-</div>
                                        @else
                                            <div class="mx-auto flex items-center justify-center text-slate-100 dark:text-slate-800/50 font-bold text-[10px]">-</div>
                                        @endif
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 1 }}" class="px-6 py-16 text-center">
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
            
                        @if($paginatedReports instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginatedReports->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $paginatedReports->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>

<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 280px !important;
        }
    }
</style>
