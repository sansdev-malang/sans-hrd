<x-admin-layout>
    <div class="p-6 space-y-6">
        
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Laporan Persentase Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Bahan evaluasi kehadiran, kedisplinan, dan pemenuhan hari kerja pegawai untuk pimpinan unit sekolah.</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Periode Cutoff: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ \Carbon\Carbon::parse($startDateReq)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDateReq)->format('d M Y') }}</strong></span>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('attendance-percentage-reports.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-end gap-3 w-full">
                
                <!-- Bulan -->
                <div class="space-y-1 w-full sm:w-40">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                    <input type="month" name="month" value="{{ request('month', $month) }}" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer">
                </div>

                <!-- Filter Unit -->
                @if(isset($schoolUnits) && count($schoolUnits) > 0)
                <div class="space-y-1 w-full sm:w-48">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                    <select name="unit_id" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer">
                        <option value="">Semua Unit</option>
                        @foreach($schoolUnits as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari nama atau NIP..."
                            style="border: none !important; outline: none !important; box-shadow: none !important;"
                            class="w-full h-9 px-3 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-0">
                        
                        <!-- Clear Button (x) -->
                        <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('form').submit();" class="h-9 px-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center" title="Bersihkan pencarian">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>

                        <button type="submit" 
                            :class="searchVal.trim() !== '' ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300'"
                            class="h-9 px-4 font-bold text-xs transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center justify-center border-l border-slate-200 dark:border-slate-800">
                            Cari
                        </button>
                    </div>

                <!-- Apply Buttons -->
                <div class="flex items-center gap-2.5">
                    <button type="submit" class="w-full sm:w-auto h-9 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                        Terapkan
                    </button>
                    @if(request()->hasAny(['unit_id', 'search']) && count(request()->except('page')) > 0)
                        <a href="{{ route('attendance-percentage-reports.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 {{ empty($unitId) ? 'md:grid-cols-3' : '' }} gap-4 w-full text-left">
            @foreach($schoolUnits as $unit)
                @if(empty($unitId) || $unitId == $unit->id)
                    @php
                        $stat = $unitStats[$unit->id] ?? ['average' => 0, 'count' => 0];
                        $avg = $stat['average'];
                        if ($avg >= 95) {
                            $theme = 'border-emerald-250 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 dark:border-emerald-900/30';
                            $textClass = 'text-emerald-600 dark:text-emerald-400';
                        } elseif ($avg >= 90) {
                            $theme = 'border-amber-250 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-900/30';
                            $textClass = 'text-amber-600 dark:text-amber-400';
                        } else {
                            $theme = 'border-rose-250 bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-400 dark:border-rose-900/30';
                            $textClass = 'text-rose-600 dark:text-rose-400';
                        }
                    @endphp
                    <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Rata-Rata Kehadiran</span>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50 leading-tight">Unit {{ $unit->name }}</h4>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $stat['count'] }} Pegawai aktif</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-px bg-slate-100 dark:bg-slate-800"></div>
                            <div class="text-right">
                                <span class="text-2xl font-black font-mono tracking-tight block {{ $textClass }}">{{ $avg }}%</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase border {{ $theme }}">
                                    {{ $avg >= 95 ? 'Amat Baik' : ($avg >= 90 ? 'Baik' : 'Kurang') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- TABLE SECTION -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                <table class="w-full text-xs border-collapse">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-900 z-10 shadow-xs">
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/95 backdrop-blur-xs">
                            <th class="px-6 py-4 text-left font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50/90 dark:bg-slate-900/95 backdrop-blur-xs">Pegawai</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center">Hari Kerja</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-emerald-500 uppercase tracking-wider text-center bg-emerald-50/20 dark:bg-emerald-950/10">Hadir</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-red-500 uppercase tracking-wider text-center bg-red-50/20 dark:bg-red-950/10">Sakit</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-amber-500 uppercase tracking-wider text-center bg-amber-50/20 dark:bg-amber-950/10">Izin</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-blue-500 uppercase tracking-wider text-center bg-blue-50/20 dark:bg-blue-950/10">Cuti</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-rose-600 uppercase tracking-wider text-center bg-rose-50/20 dark:bg-rose-950/10">Alpa</th>
                            <th class="px-6 py-4 text-right font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider w-40">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($reports as $index => $rep)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/10 transition-colors">
                                <td class="px-6 py-4 text-slate-900 dark:text-slate-50">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($rep['employee']['photo'] ?? null)
                                            @php
                                                $photoPath = str_contains($rep['employee']['photo'], 'photos/') ? $rep['employee']['photo'] : 'photos/' . $rep['employee']['photo'];
                                                $photoUrl = rtrim($rep['employee']['unit_url'], '/') . '/storage/' . $photoPath;
                                            @endphp
                                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-750 dark:text-slate-350 shrink-0 border border-slate-200/20 dark:border-slate-800/40">
                                                {{ strtoupper(substr($rep['employee']['name'] ?? 'P', 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="text-slate-900 dark:text-slate-50 font-bold tracking-tight block">{{ $rep['employee']['name'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block">NIP: {{ $rep['employee']['nuptk_nip_nik'] ?? '-' }} &bull; Unit: {{ strtoupper($rep['employee']['unit_name'] ?? '-') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-slate-800 dark:text-slate-300">
                                    {{ $rep['total_work_days'] }} Hari
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-50/10 dark:bg-emerald-950/5">
                                    {{ $rep['total_present'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-red-500 dark:text-red-400 bg-red-50/10 dark:bg-red-950/5">
                                    {{ $rep['total_sakit'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-amber-500 dark:text-amber-400 bg-amber-50/10 dark:bg-amber-950/5">
                                    {{ $rep['total_izin'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-blue-500 dark:text-blue-400 bg-blue-50/10 dark:bg-blue-950/5">
                                    {{ $rep['total_cuti'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-rose-600 dark:text-rose-400 bg-rose-50/10 dark:bg-rose-950/5">
                                    {{ $rep['total_absent'] }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @php
                                        $percent = $rep['percentage'];
                                        if ($percent >= 95) {
                                            $color = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30';
                                        } elseif ($percent >= 90) {
                                            $color = 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30';
                                        } else {
                                            $color = 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide font-mono {{ $color }}">
                                        {{ $percent }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="info" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="font-medium text-xs">Tidak ada data pegawai atau log kehadiran untuk periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FORMULA EXPLANATION NOTE -->
        <section class="bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl p-4 w-full text-left space-y-2">
            <h4 class="text-xs font-bold text-slate-900 dark:text-slate-50 flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4 text-indigo-500"></i>
                Metodologi Perhitungan Persentase Kehadiran
            </h4>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1.5 leading-relaxed font-medium">
                <p>Rumus perhitungan persentase kehadiran pegawai adalah:</p>
                <div class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-lg inline-block font-mono text-[10px] font-bold text-slate-800 dark:text-slate-350">
                    Persentase = ( Hari Hadir / ( Hari Kerja - ( Sakit + Izin + Cuti ) ) ) &times; 100%
                </div>
                <p>Keterangan:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    <li><strong>Hari Kerja</strong>: Total hari kerja yang dijadwalkan (dikurangi hari libur sekolah dan hari off shift).</li>
                    <li><strong>Hadir</strong>: Pegawai melakukan scan masuk/pulang fisik OR memiliki izin dengan kategori <strong>Hadir (H)</strong> seperti Dinas Luar/Kedinasan.</li>
                    <li><strong>Sakit, Izin, Cuti</strong>: Hari di mana pegawai mengajukan izin dan disetujui, hari ini dikecualikan (mengurangi pembagi total hari kerja) secara proporsional.</li>
                    <li><strong>Alpa</strong>: Hari kerja terjadwal di mana pegawai tidak melakukan scan absensi fisik dan tidak memiliki surat izin/cuti yang disetujui.</li>
                </ul>
            </div>
        </section>

    </div>
</x-admin-layout>

