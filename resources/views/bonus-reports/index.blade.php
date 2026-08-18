<x-admin-layout>
    <div class="p-6 space-y-6" x-data="bonusReports">
        <!-- BONUS REPORT MAIN CONTAINER -->
        <div id="bonus-table-container" class="space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Bonus Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Evaluasi kehadiran pegawai berdasarkan skema bonus aktif.</p>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                @if($activeSchema)
                    <div class="px-3 h-9 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">Skema Aktif: <strong>{{ $activeSchema->name }}</strong></span>
                    </div>
                @else
                    <div class="px-3 h-9 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 dark:text-red-400"></i>
                        <span class="text-[11px] font-semibold text-red-800 dark:text-red-300">Tidak ada skema bonus aktif!</span>
                    </div>
                @endif

                <!-- EXPORT DROPDOWN -->
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="h-9 px-4 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Ekspor Data</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                        <a href="{{ route('bonus-reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                            Excel (.xlsx)
                        </a>
                        <a href="{{ route('bonus-reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-600 dark:text-rose-500"></i>
                            PDF (.pdf)
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTERS & CONTROLS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('bonus-reports.index') }}" id="bonus-filter-form" data-no-loader="true" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Box -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari pegawai..."
                             style="border: none !important; outline: none !important; box-shadow: none !important;"
                             class="w-full h-9 px-3 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-0">
                        
                        <!-- Clear Button (x) -->
                        <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('.search-container').querySelector('input').focus();" class="h-9 px-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center" title="Bersihkan pencarian">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>

                        <button type="submit" 
                            :class="searchVal.trim() !== '' ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300'"
                            class="h-9 px-4 font-bold text-xs transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center justify-center border-l border-slate-200 dark:border-slate-800">
                            Cari
                        </button>
                    </div>

                    <!-- Bulan -->
                    <input type="month" name="month" value="{{ request('month', $month) }}" onchange="triggerFilterForm(this)"
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer shadow-inner">

                    <!-- Filter Unit -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                        <select name="unit_id" onchange="triggerFilterForm(this)"
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
                    <select name="position" onchange="triggerFilterForm(this)"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>

                    @if(request()->filled('search') || request()->filled('unit_id') || request()->filled('position') || (request()->filled('month') && request('month') != now()->format('Y-m')) || (request()->filled('per_page') && request('per_page') != 50))
                        <a href="{{ route('bonus-reports.index') }}" data-no-loader="true" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Right Side: Per Page Options -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                    <select name="per_page" onchange="triggerFilterForm(this)"
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
        
        <!-- MAIN TABLE (MATRIX LAYOUT) -->
        @php
            $start = \Carbon\Carbon::parse($startDateReq);
            $end = \Carbon\Carbon::parse($endDateReq);
            $dates = [];
            $cycleDateData = [];
            while($start <= $end) {
                $dates[] = $start->copy();
                $cycleDateData[] = [
                    'dateStr' => $start->format('Y-m-d'),
                    'day' => (int)$start->format('d'),
                    'dayOfWeek' => (int)$start->format('N'),
                ];
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
                            <th class="px-4 py-3 bg-slate-50 dark:bg-slate-900 text-left sticky top-0 md:left-0 z-30 md:z-40 border-r border-slate-200 dark:border-slate-800 min-w-[150px]">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profil Pegawai</span>
                                    <span class="text-right leading-tight">
                                        <span class="block text-[10px] font-medium text-slate-500 dark:text-slate-400">Siklus:</span>
                                        <strong class="block text-xs font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($startDateReq)->format('d M') }} - {{ \Carbon\Carbon::parse($endDateReq)->format('d M') }}</strong>
                                    </span>
                                </div>
                            </th>
                            <th class="px-4 py-3 bg-slate-50 dark:bg-slate-900 text-center sticky top-0 z-30 border-r border-slate-200 dark:border-slate-800 min-w-[120px]">
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Bonus</span>
                            </th>
                            @foreach($dates as $date)
                            @php
                                $isToday = $date->isToday();
                                $isSunday = $date->isSunday();
                                $dayColor = $isSunday && !$isToday ? 'text-red-500' : ($isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500');
                                $numColor = $isSunday && !$isToday ? 'text-red-500 dark:text-red-400' : ($isToday ? 'text-white' : 'text-slate-700 dark:text-slate-200');
                                $bgToday = $isToday ? 'bg-indigo-600 dark:bg-indigo-500 w-6 h-6 flex items-center justify-center rounded-full' : '';
                            @endphp
                            <th class="py-2 px-1 text-center sticky top-0 z-30 min-w-[32px] border-r border-slate-100 dark:border-slate-800/60 {{ $isSunday ? 'bg-red-50 dark:bg-red-950' : 'bg-slate-50 dark:bg-slate-900' }}">
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
                                        <!-- Clickable Photo/Avatar -->
                                        <div @click='openCalendarModal(@json($report))' class="cursor-pointer hover:scale-105 transform transition-all active:scale-95 duration-150 shrink-0">
                                            @if(!empty($report['employee']['photo']))
                                                <img src="{{ str_contains($report['employee']['photo'], 'photos/') ? rtrim($report['employee']['unit_url'], '/') . '/storage/' . $report['employee']['photo'] : rtrim($report['employee']['unit_url'], '/') . '/storage/photos/' . $report['employee']['photo'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200/50 dark:border-slate-800/40">
                                            @else
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm" style="background:{{ $color }}">{{ $initial }}</div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col min-w-0 text-left">
                                            <span @click='openCalendarModal(@json($report))' class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $empName }}</span>
                                            <div class="flex flex-col gap-0.5 mt-0.5">
                                                <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300 truncate max-w-[120px] inline-block w-max">{{ $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-') }}</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate" title="{{ $report['employee']['position'] ?? '-' }}">{{ $report['employee']['position'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <!-- KOLOM 2: TOTAL BONUS -->
                                <td class="px-4 py-2 border-r border-slate-100 dark:border-slate-800/60 text-center transition-colors">
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
                                <td class="py-1 px-1 text-center border-r border-slate-50 dark:border-slate-800/30 {{ $date->isSunday() ? 'bg-red-50/30 dark:bg-red-950/10' : '' }}">
                                    @if($detail)
                                        @if($detail['bonus_nominal'] > 0)
                                            @php 
                                                $nominal = $detail['bonus_nominal'];
                                                $shortNominal = ($nominal >= 1000) ? ($nominal / 1000) . 'k' : $nominal;
                                                $titleText = (isset($detail['status']) && $detail['status'] === 'Dinas') ? 'Dinas: Rp ' . number_format($nominal, 0, ',', '.') : 'Rp ' . number_format($nominal, 0, ',', '.');
                                            @endphp
                                            <div class="mx-auto w-7 h-5 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold text-[9px] rounded border border-emerald-200 dark:border-emerald-800/50" title="{{ $titleText }}">
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
                    <tfoot class="bg-slate-50 dark:bg-slate-900 sticky bottom-0 z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200 text-right bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
                                TOTAL KESELURUHAN
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400 text-center bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
                                Rp {{ number_format($totalSemuaBonus ?? 0, 0, ',', '.') }}
                            </td>
                            <td colspan="{{ count($dates) }}" class="bg-slate-50 dark:bg-slate-900"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            @if($paginatedReports instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginatedReports->total() > 0)
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedReports->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedReports->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedReports->total() }}</span>
                        pegawai
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if ($paginatedReports->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-650 flex items-center justify-center cursor-not-allowed select-none">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedReports->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-semibold text-slate-600 dark:text-slate-400">
                            Halaman {{ $paginatedReports->currentPage() }} dari {{ $paginatedReports->lastPage() }}
                        </span>

                        @if ($paginatedReports->hasMorePages())
                            <a href="{{ $paginatedReports->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-650 flex items-center justify-center cursor-not-allowed select-none">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <!-- LEGEND / STATUS EXPLANATION -->
        <div class="flex flex-wrap gap-4 items-center justify-center text-[10px] md:text-xs text-slate-500 dark:text-slate-400 mt-4 px-2">
            <span class="font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider text-[10px]">Keterangan:</span>
            <span class="text-slate-650 dark:text-slate-400">Nominal disesuaikan tingkat ketepatan waktu hadir sesuai jadwal shift</span>
        </div>

        <!-- CALENDAR DETAIL MODAL -->
        <div x-show="showCalendarModal" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Wrapper -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div @click.outside="showCalendarModal = false"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800"
                     x-transition:enter="transition ease-out duration-300 transform scale-95"
                     x-transition:enter-start="transform scale-95 opacity-0"
                     x-transition:enter-end="transform scale-100 opacity-100"
                     x-transition:leave="transition ease-in duration-200 transform scale-100"
                     x-transition:leave-start="transform scale-100 opacity-100"
                     x-transition:leave-end="transform scale-95 opacity-0">
                     
                    <!-- Modal Header -->
                    <div class="border-b border-slate-100 dark:border-slate-800/60 px-5 py-4 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Rincian Bonus Harian</span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5" x-text="selectedReport ? selectedReport.employee.name : ''"></h3>
                        </div>
                        <button type="button" @click="showCalendarModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-355 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 space-y-4 text-left">
                        <!-- Calendar Info Header -->
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 opacity-80"></i>
                                <span>Periode Cut-Off</span>
                            </div>
                            <span class="text-slate-700 dark:text-slate-300" x-text="`${startDateStr} - ${endDateStr}`"></span>
                        </div>

                        <!-- Grid Kalender -->
                        <div class="border border-slate-100 dark:border-slate-800/60 rounded-xl p-3 bg-slate-50/30 dark:bg-slate-900/10">
                            <!-- Nama-Nama Hari -->
                            <div class="grid grid-cols-7 gap-1 text-center text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                                <div>Sen</div>
                                <div>Sel</div>
                                <div>Rab</div>
                                <div>Kam</div>
                                <div>Jum</div>
                                <div class="text-red-400">Sab</div>
                                <div class="text-red-500">Min</div>
                            </div>

                            <!-- Grid Tanggal -->
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="day in calendarDays" :key="day.dateStr">
                                    <div class="aspect-square border border-slate-100 dark:border-slate-800/40 rounded-lg p-0.5 flex flex-col justify-between"
                                         :class="[
                                             day.isCurrentMonth ? (day.dateStr && new Date(day.dateStr).getDay() === 0 ? 'bg-red-50/50 dark:bg-red-950/15' : 'bg-white dark:bg-slate-900') : 'bg-slate-50/50 dark:bg-slate-950/20 opacity-40'
                                         ]">
                                         
                                        <!-- Tanggal -->
                                        <span class="text-[9px] font-semibold"
                                              :class="[
                                                  day.isCurrentMonth ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400',
                                                  (day.dateStr && new Date(day.dateStr).getDay() === 0) ? 'text-red-500 font-bold' : ''
                                              ]"
                                              x-text="day.day"></span>
                                              
                                        <!-- Bonus Nominal -->
                                        <div class="mt-auto w-full">
                                            <template x-if="selectedReport && selectedReport.daily_details[day.dateStr]">
                                                <div class="w-full">
                                                    <template x-if="selectedReport.daily_details[day.dateStr].bonus_nominal > 0">
                                                        <div class="w-full py-0.5 text-center bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded text-[7px] font-bold"
                                                             x-text="(selectedReport.daily_details[day.dateStr].bonus_nominal >= 1000) ? (selectedReport.daily_details[day.dateStr].bonus_nominal / 1000) + 'k' : selectedReport.daily_details[day.dateStr].bonus_nominal"></div>
                                                    </template>
                                                    <template x-if="!selectedReport.daily_details[day.dateStr].bonus_nominal || selectedReport.daily_details[day.dateStr].bonus_nominal <= 0">
                                                        <div class="text-center text-slate-300 dark:text-slate-600 text-[8px] font-bold">-</div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/30 px-5 py-3 flex flex-row items-center justify-between gap-4">
                        <div class="flex flex-wrap gap-2.5 text-[9px] md:text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Ringkasan:</span>
                            <span class="text-slate-600 dark:text-slate-400">Total: <strong class="text-emerald-650 dark:text-emerald-400" x-text="formatRupiah(stats.totalBonus)"></strong></span>
                            <span class="text-slate-600 dark:text-slate-400">Dapat Bonus: <strong class="text-indigo-650 dark:text-indigo-400 font-bold" x-text="stats.bonusDays + ' Hari'"></strong></span>
                        </div>
                        <button type="button" @click="showCalendarModal = false" class="w-full sm:w-auto h-7 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</x-admin-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bonusReports', () => ({
            showCalendarModal: false,
            selectedReport: null,
            startDateStr: '{{ \Carbon\Carbon::parse($startDateReq)->translatedFormat("d M Y") }}',
            endDateStr: '{{ \Carbon\Carbon::parse($endDateReq)->translatedFormat("d M Y") }}',
            cycleDates: @json($cycleDateData),
            calendarDays: [],
            stats: { totalBonus: 0, bonusDays: 0 },
            
            openCalendarModal(report) {
                this.selectedReport = report;
                this.calendarDays = this.buildCutOffCalendar(this.cycleDates);
                this.stats = this.calculateStats(report);
                this.showCalendarModal = true;
            },
            calculateStats(report) {
                let stats = { totalBonus: 0, bonusDays: 0 };
                if (!report) return stats;
                
                stats.totalBonus = parseFloat(report.bonus_nominal) || 0;
                
                if (report.daily_details) {
                    Object.values(report.daily_details).forEach(function(day) {
                        const nominal = parseFloat(day.bonus_nominal);
                        if (!isNaN(nominal) && nominal > 0) {
                            stats.bonusDays++;
                        }
                    });
                }
                return stats;
            },
            buildCutOffCalendar(cycleDates) {
                if (!cycleDates || cycleDates.length === 0) return [];
                
                const days = [];
                const firstDate = cycleDates[0];
                const lastDate = cycleDates[cycleDates.length - 1];
                
                // Pad start of the week (Monday is 1, Sunday is 7)
                for (let i = 1; i < firstDate.dayOfWeek; i++) {
                    days.push({ day: '', isCurrentMonth: false, dateStr: 'pad-start-' + i });
                }
                
                // Add all dates from the cycle
                cycleDates.forEach(function(d) {
                    days.push({ day: d.day, isCurrentMonth: true, dateStr: d.dateStr });
                });
                
                // Pad end of the week
                for (let i = 1; i <= (7 - lastDate.dayOfWeek); i++) {
                    days.push({ day: '', isCurrentMonth: false, dateStr: 'pad-end-' + i });
                }
                
                return days;
            },
            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(amount);
            }
        }));
    });
</script>

<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 280px !important;
        }
    }
</style>

<!-- AJAX NAVIGATION & FILTER SCRIPT -->
<script>
    function triggerFilterForm(el) {
        const form = document.getElementById('bonus-filter-form');
        if (form) {
            form.requestSubmit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('bonus-table-container');

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
                const newContent = doc.getElementById('bonus-table-container');
                
                if (newContent && container) {
                    container.innerHTML = newContent.innerHTML;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';

                    // Reinitialize Lucide Icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Reinitialize Alpine on the new elements if needed, but since Alpine x-data is on a wrapper above container, it handles live reactivity automatically.
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
            });
        }

        // Delegate submit event
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('#bonus-filter-form');
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
            const link = e.target.closest('#bonus-table-container a');
            if (!link) return;

            // Don't intercept download/export buttons or external links
            if (link.classList.contains('reset-filter-btn') || link.getAttribute('data-no-loader') === 'true') {
                if (link.getAttribute('href')) {
                    e.preventDefault();
                    loadTableContent(link.getAttribute('href'));
                }
                return;
            }

            const href = link.getAttribute('href');
            if (href && (href.startsWith(window.location.origin) || href.startsWith('/'))) {
                // If it is an export link, let it download naturally
                if (href.includes('/export')) {
                    return;
                }
                e.preventDefault();
                loadTableContent(href);
            }
        });
    });
</script>

