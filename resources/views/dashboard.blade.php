<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Dashboard Aggregator</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Memonitor kehadiran dan data pegawai secara gabungan dari seluruh unit sekolah.</p>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 shadow-sm">
                <!-- Unit Tag Indicator -->
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    Unit Sekolah SD
                </span>
            </div>
        </header>

        <!-- STATS SECTION -->
        <section class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Total Pegawai -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Pegawai SD</span>
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
                    <span class="text-2xl font-bold text-amber-500 dark:text-amber-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $sakit }}">{{ $sakit }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="activity" class="w-5 h-5"></i>
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

            <!-- Alpa / Belum Absen -->
            <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between col-span-2 md:col-span-1">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Belum Absen</span>
                    <span class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 block">
                        <span class="stat-counter" data-target="{{ $belumAbsen }}">{{ $belumAbsen }}</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full">
            <form method="GET" action="#" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Absensi</label>
                        <input type="date" name="date" value="{{ $date }}"
                            class="h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                    </div>
                    <div class="pt-5">
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center justify-center gap-2">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- DATA LIST -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-850 dark:text-slate-100">Daftar Status Pegawai Unit SD (Tanggal: {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y') }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Tipe Pegawai</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">NUPTK/NIP/NIK</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Status Absen</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Jam Masuk</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($sdEmployees as $index => $emp)
                            @php
                                $att = $attendanceMap[$emp['id']] ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-slate-50">{{ $emp['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp['subject_position'] }} • {{ $emp['gender'] == 'Male' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ $emp['employee_type']['name'] ?? 'Pegawai' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $emp['nuptk_nip_nik'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($att)
                                        @if($att['status'] == 'Present')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-800/40 uppercase">Hadir</span>
                                        @elseif($att['status'] == 'Permit')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200/40 dark:border-blue-800/40 uppercase">Izin</span>
                                        @elseif($att['status'] == 'Sick')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/40 dark:border-amber-800/40 uppercase">Sakit</span>
                                        @elseif($att['status'] == 'Absent')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/40 dark:border-rose-800/40 uppercase">Alpa</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-900 text-slate-650 dark:text-slate-400 border border-slate-200/40 dark:border-slate-800/45 uppercase">Belum Absen</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $att['clock_in'] ?? '-- : --' }}
                                </td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-mono">
                                    {{ $att['clock_out'] ?? '-- : --' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="users-2" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Tidak ada data pegawai yang dapat ditampilkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
