<x-admin-layout>
    <div class="p-6">
        <!-- HEADER SECTION -->
        <section class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Log Mentah Mesin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Data absensi murni yang ditarik/dipush dari mesin ZKTeco.</p>
        </section>

        <!-- MAIN CARD -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            
            <!-- FILTER & SEARCH BAR -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <form action="{{ route('raw-attendance-logs.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                    <!-- SEARCH -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari UID atau Mesin..." 
                            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>

                    <!-- BUTTON -->
                    <button type="submit" class="h-9 px-4 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-medium text-xs rounded-lg transition-colors flex items-center justify-center min-w-[100px]">
                        Terapkan
                    </button>
                    @if($search)
                    <a href="{{ route('raw-attendance-logs.index') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 font-medium text-xs rounded-lg transition-colors flex items-center justify-center">
                        Reset
                    </a>
                    @endif
                </form>
            </div>

            <!-- TABLE DESKTOP -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">ID Log</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">UID Pegawai</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Waktu (Timestamp)</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">State</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Tipe</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Mesin Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-6">{{ $log->id }}</td>
                            <td class="py-3 px-6 font-medium text-slate-900 dark:text-slate-200">{{ $log->uid }}</td>
                            <td class="py-3 px-6">{{ $log->timestamp }}</td>
                            <td class="py-3 px-6">
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded text-[10px] font-semibold">
                                    {{ $log->state }}
                                </span>
                            </td>
                            <td class="py-3 px-6">{{ $log->type }}</td>
                            <td class="py-3 px-6">
                                @if($log->device)
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $log->device->is_online ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        <span class="text-xs font-medium">{{ $log->device->name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 ml-3.5">{{ $log->device->sn }}</div>
                                @else
                                    <span class="text-slate-400 italic">Mesin Dihapus/Tidak Diketahui</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <i data-lucide="database" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
                                Belum ada log absensi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            @endif

        </section>
    </div>
</x-admin-layout>
