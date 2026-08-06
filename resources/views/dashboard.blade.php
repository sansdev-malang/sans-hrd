<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ showEmpDetailModal: false, selectedEmp: null }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Dashboard Utama</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Memonitor ringkasan aktivitas kepegawaian dan kehadiran seluruh unit sekolah.</p>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 shadow-sm">
                <!-- Unit Tag Indicator -->
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    Seluruh Unit Terhubung
                </span>
            </div>
        </header>

        <!-- STATS SECTION -->
        <section class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Total Pegawai -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Pegawai</span>
                    <span class="text-2xl font-bold text-slate-900 dark:text-slate-50 mt-1 block">
                        <span class="stat-counter" data-target="{{ $employeesCount }}">{{ $employeesCount }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Hadir -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
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
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
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
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
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
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
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
            <!-- Pengumuman Terbaru Widget -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="megaphone" class="w-4 h-4 text-indigo-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Pengumuman Terbaru</h3>
                    </div>
                    <a href="{{ route('announcements.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[300px]">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($latestAnnouncements as $ann)
                            @php
                                $catColor = [
                                    'umum' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400 border-indigo-100/40 dark:border-indigo-900/20',
                                    'akademik' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100/40 dark:border-blue-900/20',
                                    'kepegawaian' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100/40 dark:border-emerald-900/20',
                                    'penting' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400 border-rose-100/40 dark:border-rose-900/20 animate-pulse',
                                ][$ann->category] ?? 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                            @endphp
                            <a href="{{ route('announcements.show', $ann) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors text-left">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-semibold text-sm text-slate-900 dark:text-slate-100 truncate pr-2">{{ $ann->title }}</p>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border uppercase shrink-0 {{ $catColor }}">
                                        {{ $ann->category }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-slate-400 dark:text-slate-500">
                                    <span>Tanggal: {{ $ann->publish_date ? $ann->publish_date->format('d M Y') : $ann->created_at->format('d M Y') }}</span>
                                    <span class="font-medium text-slate-500">{{ $ann->creator->name ?? 'Admin' }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center text-slate-400">
                                <p class="text-xs">Belum ada pengumuman terbaru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Pengajuan Cuti Widget -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Menunggu Persetujuan Izin/Cuti</h3>
                    </div>
                    <a href="{{ route('leave-approvals.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[300px]">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @if(isset($pendingLeaves) && count($pendingLeaves) > 0)
                            @foreach($pendingLeaves as $leave)
                                <a href="{{ route('leave-approvals.index') }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-semibold text-sm text-slate-900 dark:text-slate-100 text-left">{{ $leave->employee_name ?? 'Pegawai' }}</p>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">Pending</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 text-left">
                                        Mengajukan <span class="font-semibold">{{ $leave->type }}</span> pada {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 italic text-left"><i data-lucide="info" class="w-3 h-3 inline mr-1"></i>{{ $leave->reason }}</p>
                                </a>
                            @endforeach
                        @else
                            <div class="p-8 text-center text-slate-400">
                                <p class="text-xs">Tidak ada pengajuan izin/cuti yang menunggu persetujuan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- DETAILS ROW -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <!-- Aktivitas Absensi Terkini -->
            <div class="lg-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-emerald-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Aktivitas Kehadiran Terkini</h3>
                    </div>
                </div>
                <div class="p-5 flex-1 overflow-hidden flex flex-col">
                    <div class="max-h-[400px] overflow-y-auto pr-2 custom-scrollbar flex-1">
                        <div class="border-l-2 border-slate-200 dark:border-slate-800 ml-2 space-y-5">
                            
                            @php
                            $recentAtts = collect($attendanceMap)
                                ->filter(fn($att) => isset($att['status']) && ($att['status'] == 'Present' || $att['status'] == 'Late') && (isset($att['clock_in']) || isset($att['clock_out'])))
                                ->sortByDesc(fn($att) => $att['last_activity'] ?? ($att['clock_out'] ?? $att['clock_in']));

                            // Calculate statistics per unit
                            $unitStats = [];
                            foreach ($sdEmployees as $emp) {
                                $empId = $emp['id'];
                                $unitId = $emp['unit_id'] ?? 0;
                                $unitName = $emp['unit_name'] ?? 'Lainnya';
                                $key = "{$unitId}_{$empId}";
                                
                                if (!isset($unitStats[$unitName])) {
                                    $unitStats[$unitName] = ['total' => 0, 'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'belum_absen' => 0];
                                }
                                
                                $unitStats[$unitName]['total']++;
                                
                                if (isset($attendanceMap[$key])) {
                                    $status = $attendanceMap[$key]['status'] ?? '';
                                    if ($status === 'Present' || $status === 'Late') {
                                        $unitStats[$unitName]['hadir']++;
                                    } elseif ($status === 'Sick' || $status === 'Sakit') {
                                        $unitStats[$unitName]['sakit']++;
                                    } elseif ($status === 'Leave' || $status === 'Permit') {
                                        $unitStats[$unitName]['izin']++;
                                    } elseif ($status === 'Absent' || $status === 'Alpa') {
                                        $unitStats[$unitName]['alpa']++;
                                    } else {
                                        $unitStats[$unitName]['alpa']++;
                                    }
                                } else {
                                    $unitStats[$unitName]['alpa']++;
                                }
                            }
                            @endphp

                        @forelse($recentAtts as $uniqueKey => $att)
                            @php
                                $empObj = collect($sdEmployees)->first(function($e) use ($uniqueKey) {
                                    $u = $e['unit_id'] ?? 0;
                                    $id = $e['id'];
                                    return "{$u}_{$id}" === $uniqueKey;
                                });
                                $empName = $empObj['name'] ?? 'Pegawai';
                                $empPhoto = $empObj['photo'] ?? null;
                                $empUrl = $empObj['unit_url'] ?? '';
                                $photoSrc = null;
                                if ($empPhoto) {
                                    $photoSrc = str_contains($empPhoto, 'photos/') ? rtrim($empUrl, '/') . '/storage/' . $empPhoto : rtrim($empUrl, '/') . '/storage/photos/' . $empPhoto;
                                }
                            @endphp
                            @php
                                $isCheckout = !empty($att['clock_out']) && (!isset($att['clock_in']) || $att['clock_out'] >= $att['clock_in']);
                            @endphp
                            <div class="relative pl-5">
                                <span class="absolute -left-[9px] top-2.5 w-4 h-4 rounded-full border-4 border-white dark:border-slate-900 {{ $isCheckout ? 'bg-blue-500' : 'bg-emerald-500' }}"></span>
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0 w-8 h-8">
                                        @if($photoSrc)
                                            <img src="{{ $photoSrc }}" onerror="this.remove(); document.getElementById('initials-{{ $uniqueKey }}').classList.remove('hidden');" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                        @endif
                                        <div id="initials-{{ $uniqueKey }}" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-800 shrink-0 {{ $photoSrc ? 'hidden' : '' }}">
                                            {{ strtoupper(substr($empName, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1 text-left">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $empName }}</span>
                                            @if(isset($empObj['unit_name']))
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider
                                                    @if(strtolower($empObj['unit_name']) === 'sd') bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30
                                                    @elseif(strtolower($empObj['unit_name']) === 'smp') bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-indigo-200/30 dark:border-indigo-900/30
                                                    @else bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30 @endif">
                                                    {{ $empObj['unit_name'] }}
                                                </span>
                                            @endif
                                            @if(!empty($empObj['position']))
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">({{ $empObj['position'] }})</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                            @if(isset($att['clock_in']))
                                                Hadir <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $att['clock_in'] }}</span>
                                                @if(isset($att['clock_in_device']))
                                                    <span class="text-slate-400">({{ $att['clock_in_device'] }})</span>
                                                @endif
                                            @endif
                                            
                                            @if(isset($att['clock_out']))
                                                @if(isset($att['clock_in']))
                                                    <span class="mx-1">&bull;</span>
                                                @endif
                                                Pulang <span class="font-bold text-blue-600 dark:text-blue-400">{{ $att['clock_out'] }}</span>
                                                @if(isset($att['clock_out_device']))
                                                    <span class="text-slate-400">({{ $att['clock_out_device'] }})</span>
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-xs text-slate-400">Belum ada aktivitas kehadiran terekam hari ini.</p>
                            </div>
                        @endforelse

                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Unit Hari Ini -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-indigo-500"></i>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Statistik Kehadiran Unit</h3>
                    </div>
                </div>
                <div class="p-5 max-h-[400px] overflow-y-auto custom-scrollbar flex-1 space-y-6">
                    @forelse($unitStats as $unitName => $stats)
                        @php
                            $attendanceRate = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
                            $themeColor = 'indigo';
                            if (strtolower($unitName) === 'sd') $themeColor = 'indigo';
                            elseif (strtolower($unitName) === 'smp') $themeColor = 'emerald';
                            elseif (strtolower($unitName) === 'paud') $themeColor = 'amber';
                        @endphp
                        <div class="space-y-2 text-left">
                            <div class="flex justify-between items-center font-sans">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full 
                                        @if($themeColor === 'indigo') bg-indigo-500
                                        @elseif($themeColor === 'emerald') bg-emerald-500
                                        @else bg-amber-500 @endif"></span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase">Unit {{ $unitName }}</span>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-slate-50">{{ $attendanceRate }}% Hadir</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-slate-100 dark:bg-slate-900 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-500 
                                    @if($themeColor === 'indigo') bg-indigo-600 dark:bg-indigo-500
                                    @elseif($themeColor === 'emerald') bg-emerald-600 dark:bg-emerald-500
                                    @else bg-amber-600 dark:bg-amber-500 @endif" 
                                    style="width: {{ $attendanceRate }}%"></div>
                            </div>
                            
                            <!-- Breakdown Stats -->
                            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; margin-top: 0.25rem;" class="font-sans">
                                <!-- Hadir -->
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 border border-slate-100 dark:border-slate-700 flex flex-col items-center justify-center gap-1">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Hadir</span>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $stats['hadir'] }}</span>
                                </div>
                                <!-- Izin/Sakit -->
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 border border-slate-100 dark:border-slate-700 flex flex-col items-center justify-center gap-1">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                        <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Izin</span>
                                    </div>
                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $stats['izin'] + $stats['sakit'] }}</span>
                                </div>
                                <!-- Absen -->
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 border border-slate-100 dark:border-slate-700 flex flex-col items-center justify-center gap-1">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Absen</span>
                                    </div>
                                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 font-mono">{{ $stats['alpa'] + $stats['belum_absen'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-xs text-slate-400">Belum ada data unit terhubung.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

    </div>

    <style>
        @media (min-width: 1024px) {
            .lg-span-2 {
                grid-column: span 2 / span 2 !important;
            }
        }
    </style>
</x-admin-layout>
