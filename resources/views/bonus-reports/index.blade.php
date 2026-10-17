<x-admin-layout>
    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-5 lg:space-y-6 text-left" x-data="bonusReports" @scroll.window="hideMatrixTooltip()">
        <!-- BONUS REPORT MAIN CONTAINER -->
        <div id="bonus-table-container" class="space-y-3.5 sm:space-y-5 lg:space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Uang Kehadiran</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Evaluasi ketepatan waktu kedatangan pegawai berdasarkan skema uang kehadiran aktif.</p>
            </div>
            
            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                <!-- EXPORT DROPDOWN -->
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="h-9 px-4 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Ekspor Data</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.150ms style="display: none;" class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
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
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center flex-1 min-w-[200px] search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
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
                        <select name="unit_id" onchange="const pos = this.form.querySelector('select[name=position]'); if(pos) pos.value = ''; triggerFilterForm(this)"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua</option>
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
                            <option value="{{ $pos }}" {{ ($position ?? request('position')) == $pos ? 'selected' : '' }}>{{ $pos }}</option>
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
            <div class="overflow-x-auto" style="max-height: calc(100vh - 280px); overflow-y: auto;" @scroll="hideMatrixTooltip()">
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
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Uang Kehadiran</span>
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
                                <td class="px-4 py-2 static md:sticky md:left-0 md:z-20 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-[#162033] border-r border-slate-100 dark:border-slate-800/60 transition-colors">
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
                                    $isPicket = $detail['is_picket'] ?? false;
                                    $picketArea = $detail['picket_area'] ?? 'Area Piket';
                                    $statusName = $detail['status'] ?? '';
                                    $isReward = $statusName === 'Reward Libur' || !empty($detail['is_reward_holiday']);
                                    $isFuture = $dateStr > date('Y-m-d');
                                    $isKedinasanStatus = $statusName === 'Dinas' || 
                                        str_contains(strtolower($statusName), 'dinas') || 
                                        str_contains(strtolower($statusName), 'kedinasan');

                                    if ($detail) {
                                        if ($isFuture) {
                                            $statusBadge = 'Belum Terlaksana';
                                            $statusColor = 'slate';
                                            $noteText = 'Tanggal dan presensi belum berjalan.';
                                        } elseif ($isReward) {
                                            $statusBadge = 'Libur Reward';
                                            $statusColor = 'emerald';
                                            $noteText = 'Bebas keterlambatan (' . ($detail['reward_name'] ?? 'Libur Reward') . ').';
                                        } elseif ($isKedinasanStatus) {
                                            $shortStatus = str_contains($statusName, '(') ? trim(explode('(', $statusName)[0]) : $statusName;
                                            $statusBadge = 'Tugas Dinas';
                                            $statusColor = 'blue';
                                            $noteText = 'Dispensasi absensi tugas dinas: ' . $shortStatus . '.';
                                        } elseif ($isPicket) {
                                            $isLate = !empty($detail['late_minutes']) && $detail['late_minutes'] > 0;
                                            $statusBadge = $isLate ? 'Piket (Telat)' : 'Piket Hadir';
                                            $statusColor = $isLate ? 'amber' : 'emerald';
                                            $noteText = 'Jadwal piket di ' . $picketArea . '.';
                                        } elseif (!empty($detail['late_minutes']) && $detail['late_minutes'] > 0) {
                                            $statusBadge = 'Terlambat';
                                            $statusColor = 'amber';
                                            $noteText = 'Terlambat ' . $detail['late_minutes'] . ' menit dari batas shift.';
                                        } elseif (!empty($detail['check_in']) && $detail['check_in'] !== 'Tidak Hadir') {
                                            $statusBadge = 'Tepat Waktu';
                                            $statusColor = 'emerald';
                                            $early = $detail['early_minutes'] ?? 0;
                                            $noteText = $early > 0 ? 'Datang ' . $early . ' menit lebih awal.' : 'Tepat waktu sesuai jadwal.';
                                        } else {
                                            $statusBadge = 'Tidak Hadir';
                                            $statusColor = 'rose';
                                            $noteText = 'Tidak tercatat data presensi kedatangan.';
                                        }

                                        if ($isReward) {
                                            $shiftLabel = 'Libur Reward';
                                            $jadwalStr = 'Bebas Jam';
                                        } elseif ($isPicket) {
                                            $shiftLabel = 'Piket: ' . $picketArea;
                                            $startJam = !empty($detail['shift_start']) ? substr($detail['shift_start'], 0, 5) : '06:30';
                                            $jadwalStr = $startJam;
                                        } else {
                                            $shiftLabel = !empty($detail['shift_name']) ? $detail['shift_name'] : 'Reguler';
                                            $startJam = !empty($detail['shift_start']) ? substr($detail['shift_start'], 0, 5) : '-';
                                            $endJam = !empty($detail['shift_end']) ? substr($detail['shift_end'], 0, 5) : '';
                                            $jadwalStr = $endJam ? "{$startJam} - {$endJam}" : $startJam;
                                        }

                                        if ($isFuture) {
                                            $inStr = '-';
                                            $outStr = '-';
                                        } else {
                                            $inStr = !empty($detail['check_in']) ? $detail['check_in'] : 'Tidak Hadir';
                                            $outStr = !empty($detail['check_out']) ? $detail['check_out'] : '-';
                                        }

                                        $cellData = [
                                            'employee' => [
                                                'name' => $empName,
                                                'unit' => $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-'),
                                                'position' => $report['employee']['position'] ?? '-',
                                            ],
                                            'date_display' => $date->translatedFormat('d F Y'),
                                            'day_name' => $date->translatedFormat('l'),
                                            'is_today' => $date->isToday(),
                                            'is_sunday' => $date->isSunday(),
                                            'is_future' => $isFuture,
                                            'is_off' => false,
                                            'status' => $statusBadge,
                                            'status_color' => $statusColor,
                                            'is_picket' => $isPicket,
                                            'picket_area' => $picketArea,
                                            'is_reward' => $isReward,
                                            'reward_name' => $detail['reward_name'] ?? null,
                                            'is_dinas' => $isKedinasanStatus,
                                            'shift_name' => $shiftLabel,
                                            'jadwal' => $jadwalStr,
                                            'check_in' => $inStr,
                                            'check_out' => $outStr,
                                            'is_late' => (!empty($detail['late_minutes']) && $detail['late_minutes'] > 0),
                                            'late_minutes' => (int)($detail['late_minutes'] ?? 0),
                                            'early_minutes' => (int)($detail['early_minutes'] ?? 0),
                                            'bonus' => (int)($detail['bonus_nominal'] ?? 0),
                                            'tier_level' => $detail['tier_level'] ?? null,
                                            'schema_name' => !empty($detail['schema_name']) ? trim(preg_replace('/\s*\([^)]*toleransi[^)]*\)/i', '', $detail['schema_name'])) : null,
                                            'note' => $noteText,
                                        ];
                                    } else {
                                        $cellData = [
                                            'employee' => [
                                                'name' => $empName,
                                                'unit' => $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-'),
                                                'position' => $report['employee']['position'] ?? '-',
                                            ],
                                            'date_display' => $date->translatedFormat('d F Y'),
                                            'day_name' => $date->translatedFormat('l'),
                                            'is_today' => $date->isToday(),
                                            'is_sunday' => $date->isSunday(),
                                            'is_future' => $isFuture,
                                            'is_off' => true,
                                            'status' => $date->isSunday() ? 'Akhir Pekan' : 'Jadwal Libur (OFF)',
                                            'status_color' => $date->isSunday() ? 'rose' : 'slate',
                                            'is_picket' => false,
                                            'picket_area' => null,
                                            'is_reward' => false,
                                            'reward_name' => null,
                                            'is_dinas' => false,
                                            'shift_name' => 'Jadwal Libur Rutin',
                                            'jadwal' => 'OFF',
                                            'check_in' => '-',
                                            'check_out' => '-',
                                            'is_late' => false,
                                            'late_minutes' => 0,
                                            'early_minutes' => 0,
                                            'bonus' => 0,
                                            'tier_level' => null,
                                            'schema_name' => null,
                                            'note' => $date->isSunday() ? 'Hari libur akhir pekan.' : 'Hari libur rutin pegawai.',
                                        ];
                                    }
                                @endphp
                                <td class="group/cell py-1 px-1 text-center border-r border-slate-50 dark:border-slate-800/30 cursor-pointer transition-all duration-75 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:ring-1.5 hover:ring-inset hover:ring-indigo-400 dark:hover:ring-indigo-500/70 {{ $date->isSunday() ? 'bg-red-50/30 dark:bg-red-950/10' : '' }}"
                                    @mouseenter="showMatrixTooltip($event, @js($cellData))"
                                    @mouseleave="hideMatrixTooltip()"
                                    @click.stop="toggleMatrixTooltip($event, @js($cellData))">
                                    @if($detail)
                                        @if(($detail['bonus_nominal'] ?? 0) > 0)
                                             @php 
                                                $nominal = $detail['bonus_nominal'];
                                                $shortNominal = ($nominal >= 1000) ? ($nominal / 1000) . 'k' : $nominal;
                                            @endphp
                                            <div class="relative mx-auto w-7 h-5 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold text-[9px] rounded border group-hover/cell:scale-110 group-hover/cell:shadow-xs transition-transform duration-75 {{ $isPicket ? 'border-amber-400 dark:border-amber-500 shadow-sm' : 'border-emerald-200 dark:border-emerald-800/50' }}">
                                                {{ $shortNominal }}
                                                @if($isPicket)
                                                    <span class="absolute -top-1 -right-1 flex h-2 w-2">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            @if($dateStr > date('Y-m-d'))
                                                <div class="mx-auto flex items-center justify-center text-slate-300 dark:text-slate-700 font-medium text-[9px] group-hover/cell:scale-110 transition-transform duration-75 {{ $isPicket ? 'text-amber-500 font-bold' : '' }}">-</div>
                                            @else
                                                <div class="relative mx-auto w-7 h-5 flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-bold text-[9px] rounded border group-hover/cell:scale-110 group-hover/cell:shadow-xs transition-transform duration-75 {{ $isPicket ? 'border-amber-400 dark:border-amber-500' : 'border-red-200 dark:border-red-800/50' }}">
                                                    0k
                                                    @if($isPicket)
                                                        <span class="absolute -top-1 -right-1 flex h-2 w-2">
                                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        @if($date->isSunday())
                                            <div class="mx-auto flex items-center justify-center text-red-400/80 dark:text-red-900/50 font-bold text-[9px]">OFF</div>
                                        @else
                                            <div class="mx-auto flex items-center justify-center text-slate-400 dark:text-slate-500 font-bold text-[9px]">OFF</div>
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
             x-transition:enter="transition ease-out "
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in "
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Wrapper -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div @click.outside="showCalendarModal = false"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800"
                     x-transition:enter="transition ease-out  transform scale-95"
                     x-transition:enter-start="transform scale-95 opacity-0"
                     x-transition:enter-end="transform scale-100 opacity-100"
                     x-transition:leave="transition ease-in  transform scale-100"
                     x-transition:leave-start="transform scale-100 opacity-100"
                     x-transition:leave-end="transform scale-95 opacity-0">
                     
                    <!-- Modal Header -->
                    <div class="border-b border-slate-100 dark:border-slate-800/60 px-5 py-4 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Rincian Uang Kehadiran Harian</span>
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
                                    <div class="aspect-square border border-slate-100 dark:border-slate-800/40 rounded-lg p-0.5 flex flex-col justify-between relative cursor-pointer"
                                         @mouseenter="showMatrixTooltipFromModal($event, day)"
                                         @mouseleave="hideMatrixTooltip()"
                                         @click.stop="toggleMatrixTooltipFromModal($event, day)"
                                         :class="[
                                             day.isCurrentMonth ? (day.dateStr && new Date(day.dateStr).getDay() === 0 ? 'bg-red-50/50 dark:bg-red-950/15' : 'bg-white dark:bg-slate-900') : 'bg-slate-50/50 dark:bg-slate-950/20 opacity-40',
                                             (selectedReport && selectedReport.daily_details[day.dateStr] && selectedReport.daily_details[day.dateStr].is_picket) ? 'border-amber-400 dark:border-amber-500/80 ring-1 ring-amber-400/30' : ''
                                         ]">
                                         
                                        <!-- Header Tanggal & Picket / Reward Icon -->
                                        <div class="flex items-center justify-between w-full px-0.5">
                                            <span class="text-[9px] font-semibold"
                                                  :class="[
                                                      day.isCurrentMonth ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400',
                                                      (day.dateStr && new Date(day.dateStr).getDay() === 0) ? 'text-red-500 font-bold' : ''
                                                  ]"
                                                  x-text="day.day"></span>
                                            <div class="flex items-center gap-0.5">
                                                <template x-if="selectedReport && selectedReport.daily_details[day.dateStr] && selectedReport.daily_details[day.dateStr].is_reward_holiday">
                                                    <span class="text-[8px] leading-none text-emerald-500 font-bold" :title="'Reward: ' + (selectedReport.daily_details[day.dateStr].reward_name || 'Reward Libur')">🎁</span>
                                                </template>
                                                <template x-if="selectedReport && selectedReport.daily_details[day.dateStr] && selectedReport.daily_details[day.dateStr].is_picket">
                                                    <span class="text-[8px] leading-none text-amber-500 dark:text-amber-400 font-bold" :title="'Piket: 06:30 (' + (selectedReport.daily_details[day.dateStr].picket_area || 'Area Piket') + ')' + (selectedReport.daily_details[day.dateStr].schema_name ? ' • [' + selectedReport.daily_details[day.dateStr].schema_name + ']' : '')">⚡</span>
                                                </template>
                                            </div>
                                        </div>
                                              
                                        <!-- Bonus Nominal -->
                                        <div class="mt-auto w-full">
                                            <template x-if="selectedReport && selectedReport.daily_details[day.dateStr]">
                                                <div class="w-full">
                                                    <template x-if="selectedReport.daily_details[day.dateStr].bonus_nominal > 0">
                                                        <div class="w-full py-0.5 text-center bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded text-[7px] font-bold"
                                                             x-text="(selectedReport.daily_details[day.dateStr].bonus_nominal >= 1000) ? (selectedReport.daily_details[day.dateStr].bonus_nominal / 1000) + 'k' : selectedReport.daily_details[day.dateStr].bonus_nominal"></div>
                                                    </template>
                                                    <template x-if="!selectedReport.daily_details[day.dateStr].bonus_nominal || selectedReport.daily_details[day.dateStr].bonus_nominal <= 0">
                                                        <div class="w-full py-0.5">
                                                            <template x-if="day.dateStr > new Date().toLocaleDateString('en-CA')">
                                                                <div class="text-center text-slate-300 dark:text-slate-700 rounded text-[7px] font-medium">-</div>
                                                            </template>
                                                            <template x-if="day.dateStr <= new Date().toLocaleDateString('en-CA')">
                                                                <div class="w-full py-0.5 text-center bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 rounded text-[7px] font-bold">0k</div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="day.isCurrentMonth && (!selectedReport || !selectedReport.daily_details[day.dateStr])">
                                                <div class="text-center text-slate-400 dark:text-slate-500 text-[7px] font-bold">OFF</div>
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
                            <span class="text-slate-600 dark:text-slate-400">Dapat Uang Kehadiran: <strong class="text-indigo-650 dark:text-indigo-400 font-bold" x-text="stats.bonusDays + ' Hari'"></strong></span>
                        </div>
                        <button type="button" @click="showCalendarModal = false" class="w-full sm:w-auto h-7 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- FLOATING RICH TOOLTIP CARD -->
        <div 
            x-show="matrixTooltip.show && matrixTooltip.data"
            x-cloak
            :style="`position: fixed; left: ${matrixTooltip.x}px; top: ${matrixTooltip.y}px; transform: translate(-50%, ${matrixTooltip.placement === 'bottom' ? '0%' : '-100%'}); z-index: 9999;`"
            class="pointer-events-none w-[285px] max-w-[calc(100vw-24px)] bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl p-3.5 text-left text-xs select-none backdrop-blur-sm"
        >
            <!-- Pointer Arrow -->
            <div 
                class="absolute w-2.5 h-2.5 rotate-45 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 transition-all duration-75"
                :class="matrixTooltip.placement === 'bottom' 
                    ? '-top-1.5 border-t border-l' 
                    : '-bottom-1.5 border-b border-r'"
                :style="`left: calc(50% + ${matrixTooltip.arrowOffset || 0}px); transform: translateX(-50%) rotate(45deg);`"
            ></div>

            <!-- Header: Pegawai & Tanggal -->
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2 mb-2.5">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <span class="font-bold text-[12px] text-slate-900 dark:text-slate-100 truncate" x-text="matrixTooltip.data?.employee?.name"></span>
                    <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0 truncate max-w-[100px]" x-text="matrixTooltip.data?.employee?.unit"></span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <div class="flex items-center gap-1.5 truncate">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate" x-text="(matrixTooltip.data?.day_name ? matrixTooltip.data.day_name + ', ' : '') + (matrixTooltip.data?.date_display || '')"></span>
                    </div>
                    <template x-if="matrixTooltip.data?.is_today">
                        <span class="text-[8px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border border-indigo-200/50 shrink-0">Hari Ini</span>
                    </template>
                </div>
            </div>

            <!-- Status Row -->
            <div class="flex items-center justify-between gap-3 mb-2.5">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium uppercase tracking-wider">Status Kehadiran</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-xs"
                      :class="{
                          'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60': matrixTooltip.data?.status_color === 'emerald',
                          'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60': matrixTooltip.data?.status_color === 'amber',
                          'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60': matrixTooltip.data?.status_color === 'rose',
                          'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60': matrixTooltip.data?.status_color === 'blue',
                          'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60': matrixTooltip.data?.status_color === 'purple',
                          'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700': matrixTooltip.data?.status_color === 'slate'
                      }"
                      x-text="matrixTooltip.data?.status">
                </span>
            </div>

            <!-- Shift & Jadwal Info -->
            <div class="flex items-center justify-between gap-2 px-2.5 py-1.5 mb-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-[10px]">
                <div class="flex items-center gap-1.5 truncate text-slate-600 dark:text-slate-400">
                    <template x-if="matrixTooltip.data?.is_picket">
                        <span class="text-amber-500 font-bold shrink-0">⚡ Piket</span>
                    </template>
                    <template x-if="!matrixTooltip.data?.is_picket">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <span class="truncate font-semibold text-slate-700 dark:text-slate-300" x-text="matrixTooltip.data?.shift_name || 'Reguler'"></span>
                </div>
                <span class="font-mono font-bold text-slate-600 dark:text-slate-300 shrink-0" x-text="matrixTooltip.data?.jadwal"></span>
            </div>

            <!-- Box Masuk & Pulang (SANS Unit Style) -->
            <template x-if="matrixTooltip.data?.is_off">
                <div class="px-3 py-2.5 mb-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 text-center">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Jadwal Libur Rutin / Off</span>
                </div>
            </template>
            <template x-if="!matrixTooltip.data?.is_off">
                <div class="grid grid-cols-2 gap-2 mb-2.5">
                    <!-- MASUK -->
                    <div class="flex flex-col bg-slate-50 dark:bg-slate-800/50 rounded-xl p-2 border border-slate-100 dark:border-slate-800 text-center">
                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Masuk</span>
                        <span class="font-extrabold text-[13px] tracking-tight"
                              :class="matrixTooltip.data?.is_late ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-100'"
                              x-text="matrixTooltip.data?.check_in || '-'">
                        </span>
                        <template x-if="matrixTooltip.data?.is_late">
                            <span class="text-[8px] font-bold text-amber-700 bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300 px-1.5 py-0.5 rounded-md mt-1 mx-auto leading-none"
                                  x-text="'Telat ' + matrixTooltip.data?.late_minutes + ' mnt'">
                            </span>
                        </template>
                        <template x-if="!matrixTooltip.data?.is_late && matrixTooltip.data?.early_minutes > 0">
                            <span class="text-[8px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded-md mt-1 mx-auto leading-none"
                                  x-text="'Awal ' + matrixTooltip.data?.early_minutes + ' mnt'">
                            </span>
                        </template>
                        <template x-if="!matrixTooltip.data?.is_late && (!matrixTooltip.data?.early_minutes || matrixTooltip.data?.early_minutes <= 0) && matrixTooltip.data?.check_in && matrixTooltip.data?.check_in !== '-' && matrixTooltip.data?.check_in !== 'Tidak Hadir'">
                            <span class="text-[8px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded-md mt-1 mx-auto leading-none">
                                Tepat Waktu
                            </span>
                        </template>
                    </div>

                    <!-- PULANG -->
                    <div class="flex flex-col bg-slate-50 dark:bg-slate-800/50 rounded-xl p-2 border border-slate-100 dark:border-slate-800 text-center">
                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Pulang</span>
                        <span class="font-extrabold text-[13px] tracking-tight text-slate-800 dark:text-slate-100"
                              x-text="matrixTooltip.data?.check_out || '-'">
                        </span>
                        <template x-if="matrixTooltip.data?.check_out && matrixTooltip.data?.check_out !== '-' && matrixTooltip.data?.check_out !== 'REWARD' && matrixTooltip.data?.check_out !== 'DINAS'">
                            <span class="text-[8px] font-medium text-slate-400 dark:text-slate-500 mt-1 leading-none">
                                Tap Pulang
                            </span>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Uang Kehadiran / Bonus Block -->
            <div class="rounded-xl p-2.5 border"
                 :class="(matrixTooltip.data?.bonus > 0) 
                     ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200/80 dark:border-emerald-800/50' 
                     : 'bg-slate-50 dark:bg-slate-800/40 border-slate-100 dark:border-slate-800'">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <div class="text-[10px] font-bold"
                             :class="(matrixTooltip.data?.bonus > 0) ? 'text-emerald-800 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">
                            Uang Kehadiran
                        </div>
                        <div class="text-[9px] text-slate-400 dark:text-slate-500 font-medium truncate max-w-[190px]">
                            <span x-text="matrixTooltip.data?.tier_level ? 'Tier ' + matrixTooltip.data.tier_level + ' • ' : ''"></span>
                            <span x-text="cleanSchemaName(matrixTooltip.data?.schema_name) || 'Skema Uang Kehadiran'"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-extrabold text-sm"
                              :class="(matrixTooltip.data?.bonus > 0) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'"
                              x-text="(matrixTooltip.data?.bonus > 0) ? 'Rp ' + Number(matrixTooltip.data.bonus).toLocaleString('id-ID') : 'Rp 0'">
                        </span>
                    </div>
                </div>

                <!-- Catatan informatif tambahan -->
                <template x-if="matrixTooltip.data?.note">
                    <div class="mt-1.5 pt-1.5 border-t text-[9.5px] leading-snug"
                         :class="(matrixTooltip.data?.bonus > 0) 
                             ? 'border-emerald-200/60 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-300' 
                             : 'border-slate-200/60 dark:border-slate-700/60 text-slate-500 dark:text-slate-400'"
                         x-text="matrixTooltip.data.note">
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
    function initBonusReportsComponent() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('bonusReports', () => ({
            showCalendarModal: false,
            selectedReport: null,
            startDateStr: '{{ \Carbon\Carbon::parse($startDateReq)->translatedFormat("d M Y") }}',
            endDateStr: '{{ \Carbon\Carbon::parse($endDateReq)->translatedFormat("d M Y") }}',
            cycleDates: @json($cycleDateData),
            calendarDays: [],
            stats: { totalBonus: 0, bonusDays: 0 },
            
            // Rich Matrix Tooltip State & Handlers
            matrixTooltipTimer: null,
            matrixTooltip: {
                show: false,
                x: 0,
                y: 0,
                placement: 'top',
                arrowOffset: 0,
                data: null,
                activeCell: '',
            },
            showMatrixTooltip(e, data) {
                if (!data) return;
                if (this.matrixTooltipTimer) clearTimeout(this.matrixTooltipTimer);
                const targetRect = e.currentTarget.getBoundingClientRect();
                
                this.matrixTooltipTimer = setTimeout(() => {
                    this.renderMatrixTooltip(targetRect, data);
                }, 75);
            },
            showMatrixTooltipInstant(e, data) {
                if (!data) return;
                if (this.matrixTooltipTimer) clearTimeout(this.matrixTooltipTimer);
                const targetRect = e.currentTarget.getBoundingClientRect();
                this.renderMatrixTooltip(targetRect, data);
            },
            renderMatrixTooltip(targetRect, data) {
                this.matrixTooltip.data = data;
                const tooltipWidth = 285;
                const tooltipHeight = 285;
                const padding = 12;
                
                const rawX = targetRect.left + (targetRect.width / 2);
                const minX = (tooltipWidth / 2) + padding;
                const maxX = window.innerWidth - (tooltipWidth / 2) - padding;
                
                const clampedX = Math.max(minX, Math.min(rawX, maxX));
                this.matrixTooltip.x = clampedX;
                
                // Calculate arrow offset relative to clamped tooltip center
                const rawArrowOffset = rawX - clampedX;
                const maxArrowOffset = (tooltipWidth / 2) - 24;
                this.matrixTooltip.arrowOffset = Math.max(-maxArrowOffset, Math.min(maxArrowOffset, rawArrowOffset));
                
                // Check if cell is too close to top of viewport
                if (targetRect.top < tooltipHeight + 20) {
                    this.matrixTooltip.y = targetRect.bottom + 8;
                    this.matrixTooltip.placement = 'bottom';
                } else {
                    this.matrixTooltip.y = targetRect.top - 8;
                    this.matrixTooltip.placement = 'top';
                }
                
                this.matrixTooltip.show = true;
            },
            hideMatrixTooltip() {
                if (this.matrixTooltipTimer) clearTimeout(this.matrixTooltipTimer);
                this.matrixTooltip.show = false;
                this.matrixTooltip.activeCell = '';
            },
            cleanSchemaName(name) {
                if (!name) return '';
                return name.replace(/\s*\([^)]*toleransi[^)]*\)/gi, '').trim();
            },
            toggleMatrixTooltip(e, data) {
                const key = (data?.employee?.name || '') + '_' + (data?.date_display || '');
                if (this.matrixTooltip.show && this.matrixTooltip.activeCell === key) {
                    this.hideMatrixTooltip();
                } else {
                    this.showMatrixTooltipInstant(e, data);
                    this.matrixTooltip.activeCell = key;
                }
            },
            showMatrixTooltipFromModal(e, day) {
                if (!day || !day.isCurrentMonth || !this.selectedReport) return;
                const detail = (this.selectedReport.daily_details && this.selectedReport.daily_details[day.dateStr]) 
                    ? this.selectedReport.daily_details[day.dateStr] 
                    : null;
                const empName = this.selectedReport.employee ? (this.selectedReport.employee.name || 'Pegawai') : 'Pegawai';
                const unitName = (this.selectedReport.employee && this.selectedReport.employee.unit) 
                    ? this.selectedReport.employee.unit.name 
                    : (this.selectedReport.employee ? (this.selectedReport.employee.unit_name || '-') : '-');
                const position = this.selectedReport.employee ? (this.selectedReport.employee.position || '-') : '-';
                
                const todayStr = new Date().toLocaleDateString('en-CA');
                const isFuture = day.dateStr > todayStr;
                const dObj = new Date(day.dateStr);
                const isSunday = dObj.getDay() === 0;

                const daysId = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthsId = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const dayName = daysId[dObj.getDay()];
                const dateDisplay = ('0' + dObj.getDate()).slice(-2) + ' ' + monthsId[dObj.getMonth()] + ' ' + dObj.getFullYear();
                
                let modalData;
                if (detail) {
                    const statusName = detail.status || '';
                    const isReward = statusName === 'Reward Libur' || !!detail.is_reward_holiday;
                    const isPicket = !!detail.is_picket;
                    const isKedinasan = statusName === 'Dinas' || statusName.toLowerCase().includes('dinas') || statusName.toLowerCase().includes('kedinasan');
                    const isLate = (detail.late_minutes && detail.late_minutes > 0);

                    let statusBadge = 'Tepat Waktu';
                    let statusColor = 'emerald';
                    let noteText = 'Tepat waktu sesuai jadwal.';

                    if (isFuture) {
                        statusBadge = 'Belum Terlaksana';
                        statusColor = 'slate';
                        noteText = 'Tanggal dan presensi belum berjalan.';
                    } else if (isReward) {
                        statusBadge = 'Libur Reward';
                        statusColor = 'emerald';
                        noteText = 'Bebas keterlambatan (' + (detail.reward_name || 'Libur Reward') + ').';
                    } else if (isKedinasan) {
                        statusBadge = 'Tugas Dinas';
                        statusColor = 'blue';
                        noteText = 'Dispensasi absensi kedinasan.';
                    } else if (isPicket) {
                        statusBadge = isLate ? 'Piket (Telat)' : 'Piket Hadir';
                        statusColor = isLate ? 'amber' : 'emerald';
                        noteText = 'Jadwal piket di ' + (detail.picket_area || 'Area Piket') + '.';
                    } else if (isLate) {
                        statusBadge = 'Terlambat';
                        statusColor = 'amber';
                        noteText = 'Terlambat ' + detail.late_minutes + ' menit dari batas shift.';
                    } else if (detail.check_in && detail.check_in !== 'Tidak Hadir') {
                        statusBadge = 'Tepat Waktu';
                        statusColor = 'emerald';
                        noteText = (detail.early_minutes > 0) ? 'Datang ' + detail.early_minutes + ' menit lebih awal.' : 'Tepat waktu sesuai toleransi.';
                    } else {
                        statusBadge = 'Tidak Hadir';
                        statusColor = 'rose';
                        noteText = 'Tidak tercatat data presensi kedatangan.';
                    }

                    let shiftLabel = 'Reguler';
                    let jadwalStr = '-';
                    if (isReward) {
                        shiftLabel = 'Libur Reward';
                        jadwalStr = 'Bebas Jam';
                    } else if (isPicket) {
                        shiftLabel = 'Piket: ' + (detail.picket_area || 'Area Piket');
                        jadwalStr = detail.shift_start ? detail.shift_start.substring(0, 5) : '06:30';
                    } else {
                        shiftLabel = detail.shift_name || 'Reguler';
                        const sJam = detail.shift_start ? detail.shift_start.substring(0, 5) : '-';
                        const eJam = detail.shift_end ? detail.shift_end.substring(0, 5) : '';
                        jadwalStr = eJam ? (sJam + ' - ' + eJam) : sJam;
                    }

                    modalData = {
                        employee: { name: empName, unit: unitName, position: position },
                        date_display: dateDisplay,
                        day_name: dayName,
                        is_today: day.dateStr === todayStr,
                        is_sunday: isSunday,
                        is_future: isFuture,
                        is_off: false,
                        status: statusBadge,
                        status_color: statusColor,
                        is_picket: isPicket,
                        picket_area: detail.picket_area || 'Area Piket',
                        is_reward: isReward,
                        reward_name: detail.reward_name || null,
                        is_dinas: isKedinasan,
                        shift_name: shiftLabel,
                        jadwal: jadwalStr,
                        check_in: isFuture ? '-' : (detail.check_in || 'Tidak Hadir'),
                        check_out: isFuture ? '-' : (detail.check_out || '-'),
                        is_late: isLate,
                        late_minutes: detail.late_minutes || 0,
                        early_minutes: detail.early_minutes || 0,
                        bonus: detail.bonus_nominal || 0,
                        tier_level: detail.tier_level || null,
                        schema_name: detail.schema_name ? this.cleanSchemaName(detail.schema_name) : null,
                        note: noteText,
                    };
                } else {
                    modalData = {
                        employee: { name: empName, unit: unitName, position: position },
                        date_display: dateDisplay,
                        day_name: dayName,
                        is_today: day.dateStr === todayStr,
                        is_sunday: isSunday,
                        is_future: isFuture,
                        is_off: true,
                        status: isSunday ? 'Akhir Pekan' : 'Jadwal Libur (OFF)',
                        status_color: isSunday ? 'rose' : 'slate',
                        is_picket: false,
                        picket_area: null,
                        is_reward: false,
                        reward_name: null,
                        is_dinas: false,
                        shift_name: 'Jadwal Libur Rutin',
                        jadwal: 'OFF',
                        check_in: '-',
                        check_out: '-',
                        is_late: false,
                        late_minutes: 0,
                        early_minutes: 0,
                        bonus: 0,
                        tier_level: null,
                        schema_name: null,
                        note: isSunday ? 'Hari libur akhir pekan.' : 'Hari libur rutin pegawai.',
                    };
                }

                this.showMatrixTooltip(e, modalData);
            },
            toggleMatrixTooltipFromModal(e, day) {
                if (!day || !day.isCurrentMonth || !this.selectedReport) return;
                const empName = this.selectedReport.employee ? (this.selectedReport.employee.name || 'Pegawai') : 'Pegawai';
                const key = empName + '_' + day.dateStr;
                if (this.matrixTooltip.show && this.matrixTooltip.activeCell === key) {
                    this.hideMatrixTooltip();
                } else {
                    this.showMatrixTooltipFromModal(e, day);
                    this.matrixTooltip.activeCell = key;
                }
            },
            
            openCalendarModal(report) {
                this.selectedReport = report;
                this.calendarDays = this.buildCutOffCalendar(this.cycleDates);
                this.stats = this.calculateStats(report);
                this.showCalendarModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
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
            },
            buildTooltip(detail, dateStr) {
                if (!detail) return '';
                const isFuture = dateStr > new Date().toLocaleDateString('en-CA');
                const isReward = (detail.status === 'Reward Libur' || !!detail.is_reward_holiday);
                const isPicket = !!detail.is_picket;
                const isKedinasan = (detail.status && (detail.status === 'Dinas' || detail.status.toLowerCase().includes('dinas')));

                let lines = [];

                // 1. Shift / Piket / Reward info
                if (isReward) {
                    lines.push('🎁 REWARD LIBUR: ' + (detail.reward_name || 'Libur Reward'));
                } else if (isPicket) {
                    lines.push('⚡ PIKET: ' + (detail.picket_area || 'Area Piket'));
                } else if (isKedinasan) {
                    lines.push('💼 TUGAS DINAS: ' + detail.status);
                } else if (detail.shift_name) {
                    lines.push('🏢 SHIFT: ' + detail.shift_name);
                } else {
                    lines.push('🏢 SHIFT: Reguler');
                }

                // 2. Jadwal
                if (!isReward) {
                    let startJam = detail.shift_start ? detail.shift_start.substring(0, 5) : (isPicket ? '06:30' : '-');
                    let endJam = detail.shift_end ? detail.shift_end.substring(0, 5) : '';
                    let jadwal = endJam ? `${startJam} - ${endJam}` : startJam;
                    lines.push('⏰ Jadwal  : ' + jadwal);
                }

                // 3. Masuk & Pulang
                if (isFuture) {
                    lines.push('📥 Masuk   : -');
                    lines.push('📤 Pulang  : -');
                } else {
                    lines.push('📥 Masuk   : ' + (detail.check_in || 'Tidak Hadir'));
                    lines.push('📤 Pulang  : ' + (detail.check_out || '-'));
                }

                // 4. Keterlambatan
                if (isFuture) {
                    lines.push('⏱️ Status  : Belum Terlaksana');
                } else if (isReward) {
                    lines.push('⏱️ Status  : Bebas Keterlambatan (Reward)');
                } else if (isKedinasan) {
                    lines.push('⏱️ Status  : Dispensasi Tugas Dinas');
                } else if (detail.late_minutes && detail.late_minutes > 0) {
                    lines.push(`⏱️ Terlambat: ${detail.late_minutes} menit`);
                } else if (detail.check_in && detail.check_in !== 'Tidak Hadir') {
                    if (detail.early_minutes && detail.early_minutes > 0) {
                        lines.push(`⏱️ Terlambat: 0 menit (Lebih awal ${detail.early_minutes} mnt)`);
                    } else {
                        lines.push('⏱️ Terlambat: 0 menit (Tepat Waktu)');
                    }
                } else {
                    lines.push('⏱️ Status  : Tidak Hadir / Alfa');
                }

                // 5. Uang Kehadiran
                const nominal = detail.bonus_nominal || 0;
                const tierStr = detail.tier_level ? ` (Tier ${detail.tier_level})` : '';
                if (nominal > 0) {
                    lines.push('💰 Uang Kehadiran : Rp ' + Number(nominal).toLocaleString('id-ID') + tierStr);
                } else {
                    lines.push('💰 Uang Kehadiran : Rp 0' + (isFuture ? '' : ' (Tidak Dapat)'));
                }

                // 6. Skema
                if (detail.schema_name) {
                    lines.push('📋 Skema   : ' + detail.schema_name);
                }

                return lines.join('\n');
            }
        }));
        }
    }

    if (window.Alpine) {
        initBonusReportsComponent();
    } else {
        document.addEventListener('alpine:init', initBonusReportsComponent);
    }
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

                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        window.Alpine.initTree(container);
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

