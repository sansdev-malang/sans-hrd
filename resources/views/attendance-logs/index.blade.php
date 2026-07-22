<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- SUCCESS/ERROR ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-955/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-955/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Gagal</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- GREETING / PAGE TITLE -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Log Absensi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Melihat daftar seluruh log absensi yang telah ditarik dari mesin ZKTeco.</p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('attendance-logs.export', request()->all()) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer" style="background-color: #16a34a; color: white;">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    Export Excel
                </a>
            </div>
        </section>

        <!-- FILTER CARD -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('attendance-logs.index') }}" class="flex flex-col gap-4 text-xs">
                <!-- TOP CONTROLS: Search and Per Page -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 border-b border-slate-100 dark:border-slate-900 pb-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Tampilkan</span>
                        <select name="per_page" class="h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            <option value="250" {{ request('per_page') == '250' ? 'selected' : '' }}>250</option>
                            <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua Data</option>
                        </select>
                        <span class="text-slate-500 dark:text-slate-400 font-medium">data</span>
                    </div>

                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau UID..." class="w-full h-9 pl-9 pr-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-shadow">
                    </div>
                </div>

                <!-- ADVANCED FILTERS -->
                <div class="flex flex-col md:flex-row items-end gap-4 text-xs">
                    
                    @if(isset($units) && count($units) > 0)
                    <div class="space-y-1 w-full md:w-48">
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                        <select name="unit_id" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="space-y-1 w-full md:w-48">
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Tanggal</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                            class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="space-y-1 w-full md:w-48">
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Absen</label>
                        <select name="state" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                            <option value="">Semua Status</option>
                            <option value="0" {{ request('state') == '0' ? 'selected' : '' }}>Masuk (Check-In)</option>
                            <option value="1" {{ request('state') == '1' ? 'selected' : '' }}>Pulang (Check-Out)</option>
                            <option value="2" {{ request('state') == '2' ? 'selected' : '' }}>Mulai Istirahat</option>
                            <option value="3" {{ request('state') == '3' ? 'selected' : '' }}>Selesai Istirahat</option>
                            <option value="15" {{ request('state') == '15' ? 'selected' : '' }}>Absen Normal</option>
                        </select>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="h-9 px-6 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['date', 'state', 'unit_id', 'search']) && count(request()->except('page')) > 0)
                            <a href="{{ route('attendance-logs.index') }}" class="inline-flex items-center justify-center h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        <!-- MAIN TABLE CARD -->
        <section class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            
            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-555 dark:text-slate-400 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Waktu Absen</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Mode Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors text-left group">
                                <td class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-500">{{ $loop->iteration + ($logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->firstItem() - 1 : 0) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        @if(isset($employeeMap[(string)$log->uid]))
                                            <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $employeeMap[(string)$log->uid] }}</span>
                                        @elseif(!empty($log->local_name))
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ $log->local_name }}</span>
                                                <span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 rounded text-[9px] font-bold uppercase tracking-wide">Data Mesin</span>
                                            </div>
                                        @else
                                            <span class="font-bold text-slate-500 dark:text-slate-400 text-sm">Tidak Dikenal</span>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-medium px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded">UID: {{ $log->uid }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ \Carbon\Carbon::parse($log->timestamp)->format('H:i:s') }}</span>
                                        <span class="text-slate-500 dark:text-slate-400 font-medium">{{ \Carbon\Carbon::parse($log->timestamp)->format('d M Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $stateConfig = [
                                            0 => ['label' => 'Masuk', 'icon' => 'log-in', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20'],
                                            1 => ['label' => 'Pulang', 'icon' => 'log-out', 'color' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'],
                                            2 => ['label' => 'Mulai Istirahat', 'icon' => 'coffee', 'color' => 'bg-sky-100 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/20'],
                                            3 => ['label' => 'Selesai Istirahat', 'icon' => 'briefcase', 'color' => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20'],
                                            15 => ['label' => 'Normal', 'icon' => 'check-circle', 'color' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'],
                                        ];
                                        $state = $stateConfig[$log->state] ?? ['label' => 'Status '.$log->state, 'icon' => 'help-circle', 'color' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border text-xs font-semibold {{ $state['color'] }}">
                                        <i data-lucide="{{ $state['icon'] }}" class="w-3.5 h-3.5"></i>
                                        {{ $state['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        @php
                                            $typeMap = [
                                                0 => ['label' => 'Password', 'icon' => 'key'],
                                                1 => ['label' => 'Sidik Jari', 'icon' => 'fingerprint'],
                                                4 => ['label' => 'Kartu RFID', 'icon' => 'credit-card'],
                                                15 => ['label' => 'Wajah', 'icon' => 'scan-face'],
                                                255 => ['label' => 'Biometrik / Auto', 'icon' => 'sparkles']
                                            ];
                                            $type = $typeMap[$log->type] ?? ['label' => 'Mode '.$log->type, 'icon' => 'help-circle'];
                                        @endphp
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                                            <i data-lucide="{{ $type['icon'] }}" class="w-3 h-3"></i>
                                            <span>Via {{ $type['label'] }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 dark:text-slate-600">
                                            <i data-lucide="inbox" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak ada data log yang ditemukan</h4>
                                            <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800/60 bg-white dark:bg-slate-950">
                    {{ $logs->links() }}
                </div>
            @endif

        </section>
    </div>
</x-admin-layout>
