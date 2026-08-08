<x-admin-layout>
    <div class="p-6 space-y-6">
        
        <!-- HEADER -->
        <section class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Rekap Bonus Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Evaluasi kehadiran pegawai berdasarkan skema bonus aktif.</p>
            </div>
            
            <div class="flex items-center gap-3">
                @if($activeSchema)
                    <div class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="text-xs font-medium text-emerald-800 dark:text-emerald-300">Skema Aktif: <strong>{{ $activeSchema->name }}</strong></span>
                    </div>
                @else
                    <div class="px-3 py-1.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 dark:text-red-400"></i>
                        <span class="text-xs font-medium text-red-800 dark:text-red-300">Tidak ada skema bonus aktif!</span>
                    </div>
                @endif

                <!-- EXPORT DROPDOWN -->
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Ekspor</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                        <a href="{{ route('bonus-reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-350 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                            Excel (.xlsx)
                        </a>
                        <a href="{{ route('bonus-reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-350 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-600 dark:text-rose-500"></i>
                            PDF (.pdf)
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTERS & CONTROLS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full">
            <form method="GET" action="{{ route('bonus-reports.index') }}" class="flex flex-wrap items-end gap-4 w-full text-left">
                
                <!-- Bulan -->
                <div class="space-y-1" style="width: 140px; flex-shrink: 0;">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                    <input type="month" name="month" value="{{ request('month', $month) }}" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer">
                </div>

                <!-- Filter Unit -->
                @if(isset($schoolUnits) && count($schoolUnits) > 0)
                <div class="space-y-1" style="width: 130px; flex-shrink: 0;">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                    <select name="unit_id" class="w-full text-xs h-9 pl-3 pr-8 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-ellipsis overflow-hidden whitespace-nowrap cursor-pointer">
                        <option value="">Semua Unit</option>
                        @foreach($schoolUnits as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Search -->
                <div class="space-y-1" style="flex-grow: 1; min-width: 180px;">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="w-full text-xs h-9 pl-9 pr-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    </div>
                </div>

                <!-- Apply & Reset Buttons -->
                <div class="space-y-1" style="flex-shrink: 0;">
                    <div class="hidden sm:block text-[10px] font-bold invisible mb-1 select-none">&nbsp;</div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="h-9 px-5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer whitespace-nowrap">
                            Terapkan
                        </button>
                        @if(request()->hasAny(['unit_id', 'search']) && count(request()->except('page')) > 0)
                            <a href="{{ route('bonus-reports.index') }}" class="inline-flex items-center justify-center h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                <!-- RIGHT SIDE: PER PAGE (Pushed to far right using auto margins) -->
                <div class="space-y-1 lg:ml-auto" style="width: 120px; flex-shrink: 0;">
                    <label class="hidden lg:block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tampilkan</label>
                    <select name="per_page" onchange="this.form.submit()" class="h-9 w-full pl-3 pr-8 text-xs font-bold border border-slate-200 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-350 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <option value="15" {{ request('per_page', 15) == '15' ? 'selected' : '' }}>15 Baris</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Baris</option>
                        <option value="all" {{ request('per_page', 15) == 'all' ? 'selected' : '' }}>Semua Data</option>
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
                            <th class="px-4 py-3 bg-slate-50 dark:bg-slate-900 text-left sticky top-0 left-auto md:left-0 z-30 md:z-40 border-r border-slate-200 dark:border-slate-800 min-w-[200px]">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profil Pegawai</span>
                                    <span class="text-right leading-tight">
                                        <span class="block text-[10px] font-medium text-slate-500 dark:text-slate-400">Siklus:</span>
                                        <strong class="block text-xs font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($startDateReq)->format('d M') }} - {{ \Carbon\Carbon::parse($endDateReq)->format('d M') }}</strong>
                                    </span>
                                </div>
                            </th>
                            <th class="px-4 py-3 bg-slate-50 dark:bg-slate-900 text-center sticky top-0 left-auto md:left-[200px] z-30 md:z-40 border-r border-slate-200 dark:border-slate-800 min-w-[120px]">
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Bonus</span>
                            </th>
                            @foreach($dates as $date)
                            @php
                                $isToday = $date->isToday();
                                $isSunday = $date->isSunday();
                                $dayColor = $isSunday && !$isToday ? 'text-red-400' : ($isToday ? '' : 'text-slate-400 dark:text-slate-500');
                                $numColor = $isSunday && !$isToday ? 'text-red-500 dark:text-red-400' : ($isToday ? 'text-white' : 'text-slate-700 dark:text-slate-200');
                                $bgToday = $isToday ? 'bg-indigo-600 dark:bg-indigo-500 w-6 h-6 flex items-center justify-center rounded-full' : '';
                            @endphp
                            <th class="py-2 px-1 text-center sticky top-0 z-30 bg-slate-50 dark:bg-slate-900 min-w-[48px] max-w-[48px] border-r border-slate-100 dark:border-slate-800/60">
                                <div class="flex flex-col items-center gap-0.5 py-0.5">
                                    <span class="text-[9px] font-semibold {{ $dayColor }} uppercase tracking-wider">{{ $date->translatedFormat('D') }}</span>
                                    <span class="text-[11px] font-bold {{ $numColor }} {{ $bgToday }}">{{ $date->format('d') }}</span>
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
                                <!-- KOLOM 2: TOTAL BONUS -->
                                <td class="px-4 py-2 static md:sticky md:left-[200px] md:z-10 bg-white dark:bg-slate-900 group-hover:bg-slate-50/60 dark:group-hover:bg-slate-900/30 border-r border-slate-100 dark:border-slate-800/60 text-center transition-colors">
                                    @if($report['bonus_nominal'] > 0)
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($report['bonus_nominal'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Rp 0</span>
                                    @endif
                                </td>
                                
                                <!-- KOLOM 3+: TANGGAL -->
                                @foreach($dates as $date)
                                @php
                                    $dateStr = $date->format('Y-m-d');
                                    $detail = $report['daily_details'][$dateStr] ?? null;
                                @endphp
                                <td class="py-1 px-1 text-center border-r border-slate-50 dark:border-slate-800/30">
                                    @if($detail)
                                        @if($detail['bonus_nominal'] > 0)
                                            @php 
                                                $nominal = $detail['bonus_nominal'];
                                                $shortNominal = ($nominal >= 1000) ? ($nominal / 1000) . 'k' : $nominal;
                                                $titleText = (isset($detail['status']) && $detail['status'] === 'Dinas') ? 'Dinas: Rp ' . number_format($nominal, 0, ',', '.') : 'Rp ' . number_format($nominal, 0, ',', '.');
                                            @endphp
                                            <div class="mx-auto w-9 h-6 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] rounded shadow-sm border border-emerald-200 dark:border-emerald-800/50" title="{{ $titleText }}">
                                                {{ $shortNominal }}
                                            </div>
                                        @else
                                            @php
                                                $titleText = (isset($detail['status']) && $detail['status'] === 'Dinas') ? 'Dinas (Tidak ada bonus)' : 'Tidak ada bonus';
                                            @endphp
                                            <div class="mx-auto flex items-center justify-center text-slate-300 dark:text-slate-600 font-bold text-xs" title="{{ $titleText }}">-</div>
                                        @endif
                                    @else
                                        @if($date->isSunday())
                                            <div class="mx-auto flex items-center justify-center text-red-200 dark:text-red-900/30 font-bold text-xs" title="Akhir Pekan">-</div>
                                        @else
                                            <div class="mx-auto flex items-center justify-center text-slate-100 dark:text-slate-800/50 font-bold text-[10px]">-</div>
                                        @endif
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 2 }}" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                        <i data-lucide="file-search" class="w-12 h-12 mb-4 text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-xs font-medium">Tidak ada data pegawai yang ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                                        </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-900/80 sticky bottom-0 z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200 text-right sticky left-0 z-40 bg-slate-50 dark:bg-slate-900/80 border-r border-slate-200 dark:border-slate-800">
                                TOTAL KESELURUHAN
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400 text-center sticky left-[200px] z-40 bg-slate-50 dark:bg-slate-900/80 border-r border-slate-200 dark:border-slate-800">
                                Rp {{ number_format($totalSemuaBonus ?? 0, 0, ',', '.') }}
                            </td>
                            <td colspan="{{ count($dates) }}" class="bg-slate-50 dark:bg-slate-900/80"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            @if($paginatedReports->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $paginatedReports->links('pagination::tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>

