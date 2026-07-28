<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ showEmpDetailModal: false, selectedEmp: null }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Dashboard Utama</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Memonitor ringkasan aktivitas kepegawaian dan kehadiran seluruh unit sekolah.</p>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 shadow-sm">
                <!-- Unit Tag Indicator -->
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    Seluruh Unit Terhubung
                </span>
            </div>
        </header>

        <!-- STATS SECTION -->
        <section class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Total Pegawai -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Pegawai</span>
                    <span class="text-2xl font-bold text-slate-900 dark:text-slate-50 mt-1 block">
                        <span class="stat-counter" data-target="{{ $employeesCount }}">{{ $employeesCount }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-650 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Hadir -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Hadir</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $hadir }}">{{ $hadir }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Sakit -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Sakit</span>
                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $sakit }}">{{ $sakit }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="thermometer" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Izin -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Izin</span>
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $izin }}">{{ $izin }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Alpa -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Alpa</span>
                    <span class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $alpa }}">{{ $alpa }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </section>

        <!-- WIDGETS SECTION -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            
            <!-- Pengajuan Cuti Widget -->
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Menunggu Persetujuan Cuti</h3>
                    </div>
                    <a href="{{ route('leave-approvals.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @if(isset($pendingLeaves) && count($pendingLeaves) > 0)
                            @foreach($pendingLeaves as $leave)
                                <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-semibold text-sm text-slate-900 dark:text-slate-100">{{ $leave->employee_name ?? 'Pegawai' }}</p>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">Pending</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Mengajukan <span class="font-semibold">{{ $leave->type }}</span> pada {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 italic"><i data-lucide="info" class="w-3 h-3 inline mr-1"></i>{{ $leave->reason }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="p-8 text-center text-slate-400">
                                <p class="text-sm">Tidak ada pengajuan cuti yang menunggu persetujuan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Aktivitas Absensi Terkini -->
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-emerald-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Aktivitas Kehadiran Terkini</h3>
                    </div>
                </div>
                <div class="p-5 flex-1">
                    <div class="border-l-2 border-slate-200 dark:border-slate-800 ml-2 space-y-5">
                        
                        @php
                            $recentAtts = collect($attendanceMap)
                                ->filter(fn($att) => isset($att['status']) && $att['status'] == 'Present' && isset($att['clock_in']))
                                ->sortByDesc('clock_in')
                                ->take(5);
                        @endphp

                        @forelse($recentAtts as $empId => $att)
                            @php
                                $empName = collect($sdEmployees)->firstWhere('id', $empId)['name'] ?? 'Pegawai';
                            @endphp
                            <div class="relative pl-5">
                                <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-4 border-white dark:border-slate-950"></span>
                                <p class="font-semibold text-sm text-slate-900 dark:text-slate-100">{{ $empName }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hadir pukul <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $att['clock_in'] }}</span></p>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-xs text-slate-400">Belum ada aktivitas kehadiran terekam hari ini.</p>
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>

        </section>

    </div>
</x-admin-layout>
