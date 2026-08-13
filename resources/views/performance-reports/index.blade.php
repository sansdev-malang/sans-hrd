<x-admin-layout>
    <div class="p-6 space-y-6">
        
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Rapor Kinerja Guru</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Rekapitulasi nilai evaluasi kinerja guru yang telah disinkronisasikan dari SANS PKG.</p>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('performance-reports.index') }}" class="flex flex-col md:flex-row items-stretch md:items-end gap-3.5 w-full">
                
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari nama guru..."
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
                </div>

                <!-- Filter Unit -->
                @if(isset($schoolUnits) && count($schoolUnits) > 0)
                <div class="space-y-1 w-full md:w-48">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                    <select name="unit_id" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Semua Unit</option>
                        @foreach($schoolUnits as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                Unit {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Filter Tahun Akademik -->
                <div class="space-y-1 w-full md:w-36">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tahun Akademik</label>
                    <select name="academic_year" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Semua Tahun</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Semester -->
                <div class="space-y-1 w-full md:w-32">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Semester</label>
                    <select name="semester" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Semua</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Ganjil (1)</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Genap (2)</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-2 w-full md:w-auto self-stretch md:self-end">
                    <button type="submit" class="flex-1 md:flex-none h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        Filter
                    </button>
                    <a href="{{ route('performance-reports.index') }}" class="flex-1 md:flex-none h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- TABLE LIST -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-450 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Nama Guru</th>
                            <th class="py-3 px-4">Unit Sekolah</th>
                            <th class="py-3 px-4">Tahun Akademik</th>
                            <th class="py-3 px-4 text-center">Semester</th>
                            <th class="py-3 px-3 text-center">Ped.</th>
                            <th class="py-3 px-3 text-center">Kpr.</th>
                            <th class="py-3 px-3 text-center">Sos.</th>
                            <th class="py-3 px-3 text-center">Prof.</th>
                            <th class="py-3 px-3 text-center">Abs.</th>
                            <th class="py-3 px-4 text-center">Nilai Akhir</th>
                            <th class="py-3 px-4 text-center">Predikat</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($reports as $report)
                            @php
                                $emp = $employeesMap->get($report->unit_id . '_' . $report->employee_id);
                            @endphp
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <!-- Name -->
                                <td class="py-3.5 px-4">
                                    @if($emp)
                                        <div class="flex items-center gap-3">
                                            @if(!empty($emp['photo']))
                                                <img src="{{ asset('storage/photos/' . $emp['photo']) }}" class="w-8 h-8 rounded-full object-cover border dark:border-slate-800 border-slate-200 shadow-sm" onerror="this.remove(); document.getElementById('initials-{{ $report->id }}').classList.remove('hidden');">
                                            @endif
                                            <div id="initials-{{ $report->id }}" class="w-8 h-8 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-extrabold text-[11px] uppercase shadow-inner {{ !empty($emp['photo']) ? 'hidden' : '' }}">
                                                {{ substr($emp['name'], 0, 2) }}
                                            </div>
                                            <div>
                                                <span class="font-bold block dark:text-slate-100 text-slate-850">{{ $emp['name'] }}</span>
                                                <span class="text-[10px] dark:text-slate-400 text-slate-500 block">{{ $emp['email'] }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Pegawai #{{ $report->employee_id }}</span>
                                    @endif
                                </td>

                                <!-- School Unit -->
                                <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $report->schoolUnit->name ?? '-' }}
                                </td>

                                <!-- TA -->
                                <td class="py-3.5 px-4 font-medium text-slate-650 dark:text-slate-400">
                                    {{ $report->academic_year }}
                                </td>

                                <!-- Semester -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $report->semester == 1 ? 'bg-amber-500/10 text-amber-600' : 'bg-blue-500/10 text-blue-600' }}">
                                        {{ $report->semester == 1 ? 'Ganjil' : 'Genap' }}
                                    </span>
                                </td>

                                <!-- Averages -->
                                <td class="py-3.5 px-3 text-center font-semibold text-slate-500 tabular-nums">
                                    {{ round($report->score_pedagogik) }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-semibold text-slate-500 tabular-nums">
                                    {{ round($report->score_kepribadian) }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-semibold text-slate-500 tabular-nums">
                                    {{ round($report->score_sosial) }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-semibold text-slate-500 tabular-nums">
                                    {{ round($report->score_profesional) }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-semibold text-slate-500 tabular-nums">
                                    {{ round($report->score_discipline) }}
                                </td>

                                <!-- Final Score -->
                                <td class="py-3.5 px-4 text-center font-black text-sm text-indigo-600 dark:text-indigo-400 tabular-nums">
                                    {{ round($report->final_score) }}
                                </td>

                                <!-- Predicate -->
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $finalScore = round($report->final_score);
                                        $badgeClass = 'bg-rose-500/10 text-rose-500 border border-rose-500/20';
                                        if ($finalScore >= 91) {
                                            $badgeClass = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
                                        } elseif ($finalScore >= 81) {
                                            $badgeClass = 'bg-teal-500/10 text-teal-650 dark:text-teal-400 border border-teal-500/20';
                                        } elseif ($finalScore >= 71) {
                                            $badgeClass = 'bg-indigo-500/10 text-indigo-650 dark:text-indigo-400 border border-indigo-500/20';
                                        } elseif ($finalScore >= 61) {
                                            $badgeClass = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                        {{ $report->predicate ?? 'Kurang Sekali' }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('performance-reports.show', $report->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-bold rounded-lg shadow-sm border dark:border-slate-700 border-slate-250 cursor-pointer transition-colors"
                                        title="Lihat Detail Rapor Kinerja">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-3 text-slate-350 dark:text-slate-700 animate-pulse"></i>
                                    <p class="text-sm font-semibold">Belum ada rapor kinerja guru yang disinkronisasikan dari SANS PKG.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($reports->hasPages())
                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                    {{ $reports->links() }}
                </div>
            @endif
        </section>

    </div>
</x-admin-layout>
