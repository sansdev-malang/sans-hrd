<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Penjadwalan Shift Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur penugasan dan rotasi shift kerja masing-masing guru & karyawan di unit-unit sekolah.</p>
            </div>
            <div>
                <a href="{{ route('employee-working-shifts.create') }}" class="h-9 px-4 inline-flex items-center justify-center bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    Tugaskan Shift Pegawai
                </a>
            </div>
        </header>

        <!-- FILTERS & LIST -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900 flex flex-col sm:flex-row justify-between gap-4">
                <form method="GET" action="{{ route('employee-working-shifts.index') }}" class="flex items-center gap-3">
                    <select name="unit_id" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-none">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="h-8 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-250 text-xs font-semibold rounded-lg shadow-sm border border-slate-250/20 dark:border-slate-800 transition-all cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-150 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left">Pegawai</th>
                            <th class="px-6 py-3 text-left">Unit</th>
                            <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($groupedAssignments as $shiftName => $group)
                            <!-- Group Header -->
                            <tr class="bg-indigo-50/50 dark:bg-indigo-900/10">
                                <td colspan="5" class="px-6 py-3 text-left">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 text-indigo-500"></i>
                                        <span class="font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide text-[11px]">{{ $shiftName }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[9px] font-bold">{{ count($group) }} Pegawai</span>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Group Items -->
                            @foreach($group as $assignment)
                                <tr>
                                    <td class="px-6 py-4 text-left">
                                        <div class="font-bold text-slate-900 dark:text-slate-50">{{ $assignment->employee_name }}</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-550 font-mono">NIP/NIK: {{ $assignment->employee_nip }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-left">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50 uppercase">
                                            {{ $assignment->schoolUnit ? $assignment->schoolUnit->name : '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono">
                                        {{ $assignment->start_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono">
                                        @if($assignment->end_date)
                                            {{ $assignment->end_date->format('d M Y') }}
                                        @else
                                            <span class="text-emerald-500 font-bold uppercase text-[9px]">Aktif Seterusnya</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('employee-working-shifts.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penjadwalan shift pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-7 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-[10px] font-semibold rounded border border-rose-250/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1 ml-auto">
                                                <i data-lucide="trash" class="w-3 h-3"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada data penugasan shift pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
