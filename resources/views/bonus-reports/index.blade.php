<x-admin-layout>
    <div class="p-6 space-y-6">
        
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Rekapan Bonus Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Evaluasi kehadiran pegawai berdasarkan skema bonus aktif.</p>
            </div>
            
            <div class="flex items-center gap-2">
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
            </div>
        </section>

        <!-- FILTERS -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full">
            <form method="GET" action="{{ route('bonus-reports.index') }}" class="flex flex-col md:flex-row items-end gap-4 text-xs">
                
                <div class="space-y-1 w-full md:w-36">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Pilih Bulan</label>
                    <input type="month" name="month" value="{{ request('month', $month) }}" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                </div>
                <div class="hidden md:flex flex-col justify-end pb-1.5">
                    <span class="text-[10px] text-slate-500 font-medium">Cut-off: <strong class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($startDateReq)->format('d M') }} - {{ \Carbon\Carbon::parse($endDateReq)->format('d M') }}</strong></span>
                </div>

                @if(isset($schoolUnits) && count($schoolUnits) > 0)
                <div class="space-y-1 w-full md:w-48">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                    <select name="unit_id" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Semua Unit</option>
                        @foreach($schoolUnits as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="space-y-1 w-full md:w-64">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="w-full h-9 pl-9 pr-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    </div>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="h-9 px-6 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                        Terapkan
                    </button>
                    @if(request()->hasAny(['unit_id', 'search']) && count(request()->except('page')) > 0)
                        <a href="{{ route('bonus-reports.index') }}" class="inline-flex items-center justify-center h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg shadow-sm transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- MAIN TABLE -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-4 text-center font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Total Hadir</th>
                            <th class="px-6 py-4 text-center font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Terlambat (Mnt)</th>
                            <th class="px-6 py-4 text-center font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Alfa (Hari)</th>
                            <th class="px-6 py-4 text-right font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Nominal Bonus</th>
                            <th class="px-6 py-4 text-center font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60" x-data="{ expanded: null }">
                        @forelse($paginatedReports as $index => $report)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors cursor-pointer" @click="expanded = expanded === {{ $index }} ? null : {{ $index }}">
                                <td class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-500">
                                    {{ $loop->iteration + ($paginatedReports->firstItem() - 1) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $report['employee']['name'] ?? 'Tidak Diketahui' }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-medium text-slate-500 dark:text-slate-400">{{ $report['employee']['nuptk_nip_nik'] ?? '-' }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300">{{ $report['employee']['unit_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold">
                                        {{ $report['total_present'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($report['total_late_minutes'] > 0)
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-bold border border-red-200 dark:border-red-800">
                                            {{ $report['total_late_minutes'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800">
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($report['total_absent'] > 0)
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-bold border border-red-200 dark:border-red-800">
                                            {{ $report['total_absent'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800">
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($report['bonus_nominal'] > 0)
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($report['bonus_nominal'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-sm font-bold text-slate-400 dark:text-slate-500">Rp 0</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-slate-400">
                                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-200" :class="{'rotate-180': expanded === {{ $index }}}"></i>
                                </td>
                            </tr>
                            <!-- EXPANDED ROW -->
                            <tr x-show="expanded === {{ $index }}" x-collapse class="bg-slate-50/30 dark:bg-slate-900/10">
                                <td colspan="7" class="p-0 border-b border-slate-200 dark:border-slate-800">
                                    <div class="p-6">
                                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                                            <i data-lucide="calendar-days" class="w-4 h-4 text-blue-500"></i>
                                            Detail Kehadiran Harian
                                        </h4>
                                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                                            <table class="w-full text-xs text-left">
                                                <thead class="bg-slate-100 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300">
                                                    <tr>
                                                        <th class="px-4 py-2">Tanggal</th>
                                                        <th class="px-4 py-2 text-center">Shift</th>
                                                        <th class="px-4 py-2 text-center">Check-In</th>
                                                        <th class="px-4 py-2 text-center">Status</th>
                                                        <th class="px-4 py-2 text-center">Telat (Mnt)</th>
                                                        <th class="px-4 py-2 text-center">Tier Harian</th>
                                                        <th class="px-4 py-2 text-right">Bonus Harian</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                                    @forelse($report['daily_details'] as $detail)
                                                    <tr class="hover:bg-white dark:hover:bg-slate-900">
                                                        <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($detail['date'])->translatedFormat('d M Y') }}</td>
                                                        <td class="px-4 py-2 text-center font-mono">{{ $detail['shift_start'] ? \Carbon\Carbon::parse($detail['shift_start'])->format('H:i') : '-' }}</td>
                                                        <td class="px-4 py-2 text-center font-mono text-emerald-600 dark:text-emerald-400">{{ $detail['check_in'] ? \Carbon\Carbon::parse($detail['check_in'])->format('H:i') : '-' }}</td>
                                                        <td class="px-4 py-2 text-center">
                                                            @if($detail['status'] == 'Present')
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Hadir</span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Alfa</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-center">
                                                            @if($detail['late_minutes'] > 0)
                                                                <span class="text-red-600 dark:text-red-400 font-bold">{{ $detail['late_minutes'] }}</span>
                                                            @else
                                                                <span class="text-slate-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-center">
                                                            @if($detail['tier_level'])
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Tier {{ $detail['tier_level'] }}</span>
                                                            @else
                                                                <span class="text-slate-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                                            {{ $detail['bonus_nominal'] > 0 ? 'Rp ' . number_format($detail['bonus_nominal'], 0, ',', '.') : '-' }}
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="7" class="px-4 py-4 text-center text-slate-500">Tidak ada jadwal shift pada periode ini.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
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
            
            @if($paginatedReports->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $paginatedReports->links('pagination::tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>
